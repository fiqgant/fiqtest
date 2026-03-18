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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkQuestionController extends Controller
{
    public function showImport(): View
    {
        $offerings = CourseOffering::with('course', 'academicPeriod')->latest()->get();
        $judge0Languages = app(Judge0Service::class)->getSupportedLanguages();

        return view('admin.questions.bulk-import', compact('offerings', 'judge0Languages'));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions');

        // Headers
        $headers = [
            'A' => 'title',
            'B' => 'difficulty',
            'C' => 'description',
            'D' => 'default_weight',
            'E' => 'starter_code',
            'F' => 'hint',
            'G' => 'reference_solution',
            'H' => 'test_cases',
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue("{$col}1", $header);
        }

        // Style headers
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Column widths
        $widths = ['A' => 30, 'B' => 12, 'C' => 60, 'D' => 16, 'E' => 40, 'F' => 40, 'G' => 40, 'H' => 50];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Wrap text for description/code columns
        $sheet->getStyle('C:G')->getAlignment()->setWrapText(true);

        // Example row
        $sheet->setCellValue('A2', 'Kuadrat Sebuah Angka');
        $sheet->setCellValue('B2', 'easy');
        $sheet->setCellValue('C2', "Diberikan sebuah bilangan bulat **n**, hitung nilai kuadratnya.\n\n**Contoh:**\n- Input: `5`\n- Output: `25`");
        $sheet->setCellValue('D2', 20);
        $sheet->setCellValue('E2', "n = int(input())\n# Tulis kode di bawah ini");
        $sheet->setCellValue('F2', "Gunakan operator perkalian `*`.\nKuadrat = n × n.");
        $sheet->setCellValue('G2', "n = int(input())\nprint(n * n)");
        $sheet->setCellValue('H2', "5||25||0;-3||9||1;0||0||0");

        // Style example row
        $sheet->getStyle('A2:H2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4FF']],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(80);

        // Notes sheet
        $notes = $spreadsheet->createSheet();
        $notes->setTitle('Notes');
        $notesData = [
            ['Column', 'Required', 'Notes'],
            ['title', 'Yes', 'Question title, max 255 characters'],
            ['difficulty', 'Yes', 'Must be: easy, medium, or hard'],
            ['description', 'Yes', 'Markdown supported. LaTeX: $$...$$ Diagram: ```mermaid'],
            ['default_weight', 'Yes', 'Numeric score weight (e.g. 10, 20.5)'],
            ['starter_code', 'No', 'Starter code shown to students. Leave blank if none.'],
            ['hint', 'No', 'Hint shown when student requests. Leave blank if none.'],
            ['reference_solution', 'No', 'Correct solution for admin reference. Leave blank if none.'],
            ['test_cases', 'Yes', "Format: input||expected_output||is_hidden(0/1)\nMultiple test cases separated by semicolon ;\nExample: 5||25||0;-3||9||1"],
        ];

        foreach ($notesData as $rowIdx => $row) {
            $notes->fromArray($row, null, "A" . ($rowIdx + 1));
        }
        $notes->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
        ]);
        $notes->getColumnDimension('A')->setWidth(20);
        $notes->getColumnDimension('B')->setWidth(10);
        $notes->getColumnDimension('C')->setWidth(70);
        $notes->getStyle('C2:C9')->getAlignment()->setWrapText(true);

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'questions-template.xlsx', [
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
            Question::create([
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

            [$title, $difficulty, $description, $defaultWeight, $starterCode, $hint, $referenceSolution, $testCasesRaw] = array_pad($row, 8, null);

            $errors = [];

            $title = trim((string) ($title ?? ''));
            $difficulty = strtolower(trim((string) ($difficulty ?? '')));
            $description = trim((string) ($description ?? ''));
            $defaultWeight = $defaultWeight !== null ? (float) $defaultWeight : null;
            $starterCode = trim((string) ($starterCode ?? ''));
            $hint = trim((string) ($hint ?? ''));
            $referenceSolution = trim((string) ($referenceSolution ?? ''));
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
                'test_cases' => $testCases,
                'errors' => $errors,
            ];
        }

        return $results;
    }
}
