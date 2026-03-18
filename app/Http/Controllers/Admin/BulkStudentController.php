<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkStudentController extends Controller
{
    public function showImport(): View
    {
        return view('admin.students.bulk-import');
    }

    public function preview(Request $request): View
    {
        $rows = $this->parseInput($request);

        return view('admin.students.bulk-preview', compact('rows'));
    }

    public function import(Request $request): RedirectResponse
    {
        if ($request->filled('parsed_rows')) {
            $valid = json_decode(base64_decode($request->input('parsed_rows')), true) ?? [];
        } else {
            $rows = $this->parseInput($request);
            $valid = array_filter($rows, fn($r) => empty($r['errors']));
        }

        $count = 0;
        $skipped_nim = [];

        foreach ($valid as $row) {
            if (Student::where('nim', $row['nim'])->exists()) {
                $skipped_nim[] = $row['nim'];
                continue;
            }
            Student::create([
                'nim'   => $row['nim'],
                'name'  => $row['name'],
                'email' => $row['email'] ?: null,
            ]);
            $count++;
        }

        $message = "{$count} student(s) imported successfully.";
        if (!empty($skipped_nim)) {
            $message .= ' ' . count($skipped_nim) . ' skipped (NIM already exists).';
        }

        return redirect()->route('admin.students.index')->with('success', $message);
    }

    private function parseInput(Request $request): array
    {
        if ($request->hasFile('file')) {
            $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);
            return $this->parseExcel($request->file('file'));
        }

        $request->validate(['csv_data' => ['required', 'string']]);
        return $this->parseCsv($request->input('csv_data'));
    }

    private function parseExcel($file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getSheetByName('Students') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        array_shift($rows); // remove header

        return $this->normalizeRows($rows);
    }

    private function parseCsv(string $raw): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $raw)));
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line);
        }
        return $this->normalizeRows($rows);
    }

    private function normalizeRows(array $rows): array
    {
        $results = [];
        foreach ($rows as $i => $row) {
            $values = array_filter($row, fn($v) => $v !== null && $v !== '');
            if (empty($values)) continue;

            [$name, $nim, $email] = array_pad($row, 3, null);

            $name  = trim((string) ($name ?? ''));
            $nim   = trim((string) ($nim ?? ''));
            $email = trim((string) ($email ?? ''));

            $errors = [];
            if ($name === '') $errors[] = 'Name is required.';
            if ($nim === '')  $errors[] = 'NIM is required.';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email format invalid.';

            $results[] = [
                'row'    => $i + 2,
                'name'   => $name,
                'nim'    => $nim,
                'email'  => $email,
                'errors' => $errors,
            ];
        }
        return $results;
    }
}
