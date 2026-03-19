<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseOffering;
use App\Models\Question;
use App\Services\Judge0Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class BulkQuestionController extends Controller
{
    public function showImport(): View
    {
        $offerings       = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();

        return view('admin.questions.bulk-import', compact('offerings', 'judge0Languages'));
    }

    public function downloadTemplate(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions');

        // Header row
        $headers = [
            'title', 'type', 'difficulty', 'description', 'default_weight',
            'starter_code', 'hint', 'reference_solution', 'tags',
            'test_cases', 'options', 'fill_blank_answer', 'true_false_answer', 'language',
        ];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue([$col + 1, 1], $header);
        }

        // Example rows
        $examples = [
            // coding
            [
                'Sum Two Numbers', 'coding', 'easy',
                'Write a function that returns the sum of two integers.',
                10, '', 'Use a + b', 'return a + b', 'math,basics',
                '2 3||5||0; -1 1||0||0', '', '', '', 'python',
            ],
            // multiple_choice
            [
                'What is 2+2?', 'multiple_choice', 'easy',
                'Choose the correct answer.',
                5, '', '', '', 'math',
                '', '*4|3|5|6', '', '', '',
            ],
            // multiple_select
            [
                'Which are prime numbers?', 'multiple_select', 'medium',
                'Select all that apply.',
                5, '', '', '', 'math',
                '', '*2|*3|4|*5', '', '', '',
            ],
            // true_false
            [
                'Is PHP a scripting language?', 'true_false', 'easy',
                'Answer true or false.',
                5, '', '', '', '',
                '', '', '', 'true', '',
            ],
            // fill_in_blank
            [
                'PHP stands for ___', 'fill_in_blank', 'easy',
                'Complete the blank.',
                5, '', '', '', '',
                '', '', 'Hypertext Preprocessor', '', '',
            ],
            // essay
            [
                'Explain OOP principles', 'essay', 'hard',
                'Describe the four main principles of object-oriented programming.',
                20, '', '', '', 'oop,concepts',
                '', '', '', '', '',
            ],
        ];

        foreach ($examples as $rowIdx => $row) {
            foreach ($row as $col => $value) {
                $sheet->setCellValue([$col + 1, $rowIdx + 2], $value);
            }
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="questions-template.xlsx"',
        ]);
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'file'               => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $rows     = $this->parseFile($request->file('file'), $request->input('default_language', ''));
        $offering = CourseOffering::with('course', 'academicPeriod')->findOrFail($request->course_offering_id);

        return view('admin.questions.bulk-preview', [
            'rows'     => $rows,
            'offering' => $offering,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
        ]);

        $totalRows = 0;

        if ($request->filled('parsed_rows')) {
            $valid     = json_decode(base64_decode($request->input('parsed_rows')), true) ?? [];
            $totalRows = count($valid);
        } else {
            $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
            $rows      = $this->parseFile($request->file('file'), $request->input('default_language', ''));
            $totalRows = count($rows);
            $valid     = array_filter($rows, fn($r) => empty($r['errors']));
        }

        $count = 0;

        foreach ($valid as $row) {
            $question = Question::create([
                'course_offering_id' => $request->course_offering_id,
                'type'               => $row['type'],
                'title'              => $row['title'],
                'slug'               => Str::slug($row['title']) . '-' . Str::lower(Str::random(6)),
                'difficulty'         => $row['difficulty'],
                'description'        => $row['description'],
                'default_weight'     => $row['default_weight'],
                'starter_code'       => $row['starter_code'] ?: null,
                'hint'               => $row['hint'] ?: null,
                'reference_solution' => $row['reference_solution'] ?: null,
                'language'           => $row['language'] ?: null,
                'test_cases'         => $row['test_cases'] ?: null,
                'fill_blank_answer'  => $row['fill_blank_answer'] ?: null,
                'true_false_answer'  => $row['true_false_answer'],
            ]);

            // Tags
            $tagIds = [];
            foreach ($row['tags'] ?? [] as $tagName) {
                $tag      = \App\Models\QuestionTag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $question->tags()->sync($tagIds);

            // Options (MC / MS)
            if (in_array($row['type'], ['multiple_choice', 'multiple_select'])) {
                foreach ($row['options'] ?? [] as $i => $opt) {
                    $question->options()->create([
                        'text'       => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                        'order'      => $i,
                    ]);
                }
            }

            $count++;
        }

        $skipped = $totalRows - $count;
        $message = "{$count} question(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped due to errors.";
        }

        return redirect()->route('admin.questions.index')->with('success', $message);
    }

    // ── Parser ────────────────────────────────────────────────────────

    private function parseFile($file, string $defaultLanguage): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getSheetByName('Questions') ?? $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Remove header row
        array_shift($rows);

        $results = [];
        $validTypes = array_keys(Question::TYPES);

        foreach ($rows as $i => $row) {
            $values = array_filter($row, fn($v) => $v !== null && $v !== '');
            if (empty($values)) {
                continue;
            }

            // Columns: A=title, B=type, C=difficulty, D=description, E=default_weight,
            //          F=starter_code, G=hint, H=reference_solution, I=tags,
            //          J=test_cases, K=options, L=fill_blank_answer, M=true_false_answer, N=language
            [
                $title, $type, $difficulty, $description, $defaultWeight,
                $starterCode, $hint, $referenceSolution, $tagsRaw,
                $testCasesRaw, $optionsRaw, $fillBlankAnswer, $trueFalseRaw, $language,
            ] = array_pad($row, 14, null);

            $errors = [];

            $title             = trim((string) ($title ?? ''));
            $type              = strtolower(trim((string) ($type ?? 'coding')));
            $difficulty        = strtolower(trim((string) ($difficulty ?? '')));
            $description       = trim((string) ($description ?? ''));
            $defaultWeight     = $defaultWeight !== null ? (float) $defaultWeight : null;
            $starterCode       = trim((string) ($starterCode ?? ''));
            $hint              = trim((string) ($hint ?? ''));
            $referenceSolution = trim((string) ($referenceSolution ?? ''));
            $tagsRaw           = trim((string) ($tagsRaw ?? ''));
            $tagNames          = $tagsRaw !== '' ? array_filter(array_map('trim', explode(',', $tagsRaw))) : [];
            $testCasesRaw      = trim((string) ($testCasesRaw ?? ''));
            $optionsRaw        = trim((string) ($optionsRaw ?? ''));
            $fillBlankAnswer   = trim((string) ($fillBlankAnswer ?? ''));
            $trueFalseRaw      = strtolower(trim((string) ($trueFalseRaw ?? '')));
            $language          = trim((string) ($language ?? '')) ?: $defaultLanguage;

            // Validations
            if ($title === '') $errors[] = 'Title is required.';
            if (! in_array($type, $validTypes)) $errors[] = "Unknown type \"{$type}\". Must be one of: " . implode(', ', $validTypes) . '.';
            if (! in_array($difficulty, ['easy', 'medium', 'hard'])) $errors[] = 'Difficulty must be easy, medium, or hard.';
            if ($description === '') $errors[] = 'Description is required.';
            if ($defaultWeight === null || $defaultWeight < 0) $errors[] = 'Default weight must be a non-negative number.';

            // Type-specific validation & parsing
            $testCases      = [];
            $options        = [];
            $trueFalseValue = null;

            if ($type === 'coding') {
                if ($testCasesRaw === '') {
                    $errors[] = 'Test cases are required for coding questions.';
                } else {
                    foreach (explode(';', $testCasesRaw) as $tc) {
                        $parts = explode('||', trim($tc));
                        if (count($parts) < 2) {
                            $errors[] = "Invalid test case format: \"{$tc}\"";
                            break;
                        }
                        $testCases[] = [
                            'input'           => str_replace('\\n', "\n", $parts[0] ?? ''),
                            'expected_output' => str_replace('\\n', "\n", $parts[1] ?? ''),
                            'is_hidden'       => ($parts[2] ?? '0') === '1',
                        ];
                    }
                }
            }

            if (in_array($type, ['multiple_choice', 'multiple_select'])) {
                if ($optionsRaw === '') {
                    $errors[] = 'Options are required for MC/MS questions (column K).';
                } else {
                    $rawParts = array_filter(array_map('trim', explode('|', $optionsRaw)));
                    if (count($rawParts) < 2) {
                        $errors[] = 'At least 2 options required. Separate with |, mark correct with * prefix.';
                    } else {
                        $hasCorrect = false;
                        foreach ($rawParts as $part) {
                            $isCorrect = str_starts_with($part, '*');
                            $options[] = [
                                'text'       => ltrim($part, '*'),
                                'is_correct' => $isCorrect,
                            ];
                            if ($isCorrect) $hasCorrect = true;
                        }
                        if (! $hasCorrect) {
                            $errors[] = 'At least one option must be marked correct (prefix with *).';
                        }
                    }
                }
            }

            if ($type === 'true_false') {
                if (in_array($trueFalseRaw, ['true', '1', 'yes'])) {
                    $trueFalseValue = true;
                } elseif (in_array($trueFalseRaw, ['false', '0', 'no'])) {
                    $trueFalseValue = false;
                } else {
                    $errors[] = 'True/false answer (column M) must be true or false.';
                }
            }

            if ($type === 'fill_in_blank' && $fillBlankAnswer === '') {
                $errors[] = 'Fill-in-blank answer is required (column L).';
            }

            $results[] = [
                'row'               => $i + 2,
                'title'             => $title,
                'type'              => $type,
                'difficulty'        => $difficulty,
                'description'       => $description,
                'default_weight'    => $defaultWeight,
                'starter_code'      => $starterCode,
                'hint'              => $hint,
                'reference_solution' => $referenceSolution,
                'tags'              => $tagNames,
                'language'          => $language,
                'test_cases'        => $testCases,
                'options'           => $options,
                'fill_blank_answer' => $fillBlankAnswer ?: null,
                'true_false_answer' => $trueFalseValue,
                'errors'            => $errors,
            ];
        }

        return $results;
    }
}
