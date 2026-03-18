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

class BulkQuestionController extends Controller
{
    public function showImport(): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();

        return view('admin.questions.bulk-import', compact('offerings', 'judge0Languages'));
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = public_path('questions-template.xlsx');

        return response()->download($path, 'questions-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'language' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $rows = $this->parseFile($request->file('file'));
        $offering = CourseOffering::with('course', 'academicPeriod')->findOrFail($request->course_offering_id);

        return view('admin.questions.bulk-preview', [
            'rows' => $rows,
            'offering' => $offering,
            'language' => $request->language,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'course_offering_id' => ['required', 'exists:course_offerings,id'],
            'language' => ['required', 'string'],
        ]);

        // From preview confirmation (parsed_rows) or fresh file upload
        $totalRows = 0;
        if ($request->filled('parsed_rows')) {
            $valid = json_decode(base64_decode($request->input('parsed_rows')), true) ?? [];
            $totalRows = count($valid);
        } else {
            $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
            $rows = $this->parseFile($request->file('file'));
            $totalRows = count($rows);
            $valid = array_filter($rows, fn($r) => empty($r['errors']));
        }

        $count = 0;

        foreach ($valid as $row) {
            $question = Question::create([
                'course_offering_id' => $request->course_offering_id,
                'title' => $row['title'],
                'slug' => Str::slug($row['title']) . '-' . Str::lower(Str::random(6)),
                'difficulty' => $row['difficulty'],
                'description' => $row['description'],
                'default_weight' => $row['default_weight'],
                'starter_code' => $row['starter_code'] ?: null,
                'hint' => $row['hint'] ?: null,
                'reference_solution' => $row['reference_solution'] ?: null,
                'language' => $request->language,
                'test_cases' => $row['test_cases'],
            ]);

            $tagIds = [];
            foreach ($row['tags'] ?? [] as $tagName) {
                $tag = \App\Models\QuestionTag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $question->tags()->sync($tagIds);

            $count++;
        }

        $skipped = $totalRows - $count;
        $message = "{$count} question(s) imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} row(s) skipped due to errors.";
        }

        return redirect()->route('admin.questions.index')->with('success', $message);
    }

    private function parseFile($file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getSheetByName('Questions') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        // Remove header row
        array_shift($rows);

        $results = [];
        foreach ($rows as $i => $row) {
            // Skip fully empty rows
            $values = array_filter($row, fn($v) => $v !== null && $v !== '');
            if (empty($values)) {
                continue;
            }

            [$title, $difficulty, $description, $defaultWeight, $starterCode, $hint, $referenceSolution, $tagsRaw, $testCasesRaw] = array_pad($row, 9, null);

            $errors = [];

            $title = trim((string) ($title ?? ''));
            $difficulty = strtolower(trim((string) ($difficulty ?? '')));
            $description = trim((string) ($description ?? ''));
            $defaultWeight = $defaultWeight !== null ? (float) $defaultWeight : null;
            $starterCode = trim((string) ($starterCode ?? ''));
            $hint = trim((string) ($hint ?? ''));
            $referenceSolution = trim((string) ($referenceSolution ?? ''));
            $tagsRaw = trim((string) ($tagsRaw ?? ''));
            $tagNames = $tagsRaw !== '' ? array_filter(array_map('trim', explode(',', $tagsRaw))) : [];
            $testCasesRaw = trim((string) ($testCasesRaw ?? ''));

            if ($title === '') $errors[] = 'Title is required.';
            if (!in_array($difficulty, ['easy', 'medium', 'hard'])) $errors[] = 'Difficulty must be easy, medium, or hard.';
            if ($description === '') $errors[] = 'Description is required.';
            if ($defaultWeight === null || $defaultWeight < 0) $errors[] = 'Default weight must be a non-negative number.';
            if ($testCasesRaw === '') $errors[] = 'Test cases are required.';

            $testCases = [];
            if ($testCasesRaw !== '') {
                foreach (explode(';', $testCasesRaw) as $tc) {
                    $parts = explode('||', trim($tc));
                    if (count($parts) < 2) {
                        $errors[] = "Invalid test case format: \"{$tc}\"";
                        break;
                    }
                    $testCases[] = [
                        'input' => str_replace('\\n', "\n", $parts[0] ?? ''),
                        'expected_output' => str_replace('\\n', "\n", $parts[1] ?? ''),
                        'is_hidden' => ($parts[2] ?? '0') === '1',
                    ];
                }
            }

            $results[] = [
                'row' => $i + 2,
                'title' => $title,
                'difficulty' => $difficulty,
                'description' => $description,
                'default_weight' => $defaultWeight,
                'starter_code' => $starterCode,
                'hint' => $hint,
                'reference_solution' => $referenceSolution,
                'tags' => $tagNames,
                'test_cases' => $testCases,
                'errors' => $errors,
            ];
        }

        return $results;
    }
}
