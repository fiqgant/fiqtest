<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class GenerateQuestionTemplate extends Command
{
    protected $signature   = 'questions:generate-template';
    protected $description = 'Generate the question import Excel template to public/questions-template.xlsx';

    public function handle(): int
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions');

        $headers = [
            'title', 'type', 'difficulty', 'description', 'default_weight',
            'starter_code', 'hint', 'reference_solution', 'tags',
            'test_cases', 'options', 'fill_blank_answer', 'true_false_answer',
            'language', 'essay_model_answer',
        ];

        // ── Header row styling ──────────────────────────────────────────
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($header);
            $cell->getStyle()->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        // ── Example rows ────────────────────────────────────────────────
        $examples = [
            // coding
            [
                'Sum Two Numbers', 'coding', 'easy',
                "Write a function that reads two integers from stdin and prints their sum.\n\nExample:\nInput: 3 5\nOutput: 8",
                20,
                "a, b = map(int, input().split())\n# your code here",
                'Use split() to read two numbers from input, then convert to int before adding.',
                "a, b = map(int, input().split())\nprint(a + b)",
                'math,basics',
                "3 5||8||0;\n-1 1||0||0;\n0 0||0||1",
                '', '', '', 'python3', '',
            ],
            // multiple_choice
            [
                'What is 2+2?', 'multiple_choice', 'easy',
                'Choose the correct answer.',
                20, '', 'Think about basic arithmetic.', '', 'math',
                '', '*4|3|5|6', '', '', '', '',
            ],
            // multiple_select
            [
                'Which are prime numbers?', 'multiple_select', 'medium',
                'Select all that apply.',
                30, '', 'A prime number is divisible only by 1 and itself.', '', 'math',
                '', '*2|*3|4|*5', '', '', '', '',
            ],
            // true_false
            [
                'Is PHP a scripting language?', 'true_false', 'easy',
                'Answer true or false.',
                20, '', 'Think about how PHP code is executed on a web server.', '', '',
                '', '', '', 'true', '', '',
            ],
            // fill_in_blank
            [
                'PHP stands for ___', 'fill_in_blank', 'easy',
                'Complete the blank with the correct expansion of the PHP acronym.',
                20, '', 'Think about what PHP is used for — it relates to web pages.', '', '',
                '', '', 'Hypertext Preprocessor', '', '', '',
            ],
            // essay
            [
                'Explain OOP principles', 'essay', 'hard',
                'Describe the four main principles of object-oriented programming with examples.',
                50, '', 'Think about the four pillars: how objects hide data, share behavior, take multiple forms, and generalize concepts.',
                '', 'oop,concepts',
                '', '', '', '', '',
                "The four OOP principles are:\n1. Encapsulation – hiding internal state, exposing only necessary interfaces.\n2. Inheritance – a child class acquiring properties/methods from a parent class.\n3. Polymorphism – objects of different classes can be treated as the same type.\n4. Abstraction – exposing only relevant details, hiding complexity.",
            ],
        ];

        foreach ($examples as $rowIdx => $row) {
            foreach ($row as $col => $value) {
                $sheet->setCellByColumnAndRow($col + 1, $rowIdx + 2, $value);
            }
            // Zebra stripe
            if ($rowIdx % 2 === 0) {
                $sheet->getStyle('A' . ($rowIdx + 2) . ':O' . ($rowIdx + 2))
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8F8FF');
            }
        }

        // ── Column widths ───────────────────────────────────────────────
        $widths = [30, 18, 12, 50, 16, 30, 40, 30, 20, 40, 30, 20, 18, 14, 50];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($w);
        }

        // Wrap text for description, hint, test_cases, essay_model_answer
        foreach ([4, 7, 10, 15] as $col) {
            $sheet->getStyleByColumnAndRow($col, 2, $col, count($examples) + 1)
                ->getAlignment()->setWrapText(true);
        }

        // ── Save ────────────────────────────────────────────────────────
        $path   = public_path('questions-template.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->info("Template saved to: {$path}");

        return self::SUCCESS;
    }
}
