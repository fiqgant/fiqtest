<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $activePeriod = AcademicPeriod::updateOrCreate(
            ['slug' => '2026-2027-odd'],
            [
                'name' => '2026/2027 Odd Semester',
                'start_date' => '2026-08-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
            ]
        );

        $nextPeriod = AcademicPeriod::updateOrCreate(
            ['slug' => '2026-2027-even'],
            [
                'name' => '2026/2027 Even Semester',
                'start_date' => '2027-01-15',
                'end_date' => '2027-06-30',
                'is_active' => false,
            ]
        );

        $algorithmCourse = Course::updateOrCreate(
            ['code' => 'IF101'],
            [
                'name' => 'Algorithm Design',
                'description' => 'Problem solving, control flow, arrays, and algorithmic thinking using code.',
            ]
        );

        $dataStructuresCourse = Course::updateOrCreate(
            ['code' => 'IF202'],
            [
                'name' => 'Data Structures',
                'description' => 'Stack, queue, hash map, recursion, and complexity analysis with implementation exercises.',
            ]
        );

        $algorithmOfferingA = CourseOffering::updateOrCreate(
            [
                'course_id' => $algorithmCourse->id,
                'academic_period_id' => $activePeriod->id,
                'class_name' => 'A',
            ],
            []
        );

        $dataStructuresOfferingB = CourseOffering::updateOrCreate(
            [
                'course_id' => $dataStructuresCourse->id,
                'academic_period_id' => $activePeriod->id,
                'class_name' => 'B',
            ],
            []
        );

        $algorithmOfferingNext = CourseOffering::updateOrCreate(
            [
                'course_id' => $algorithmCourse->id,
                'academic_period_id' => $nextPeriod->id,
                'class_name' => 'A',
            ],
            []
        );

        $students = collect([
            ['nim' => 'A11.2026.0001', 'name' => 'Rafi Pratama', 'email' => 'rafi.pratama@example.com'],
            ['nim' => 'A11.2026.0002', 'name' => 'Nadia Putri', 'email' => 'nadia.putri@example.com'],
            ['nim' => 'A11.2026.0003', 'name' => 'Bagas Saputra', 'email' => 'bagas.saputra@example.com'],
            ['nim' => 'A11.2026.0004', 'name' => 'Maya Lestari', 'email' => 'maya.lestari@example.com'],
            ['nim' => 'A11.2026.0005', 'name' => 'Dimas Aditya', 'email' => 'dimas.aditya@example.com'],
            ['nim' => 'A11.2026.0006', 'name' => 'Aulia Rahman', 'email' => 'aulia.rahman@example.com'],
            ['nim' => 'A11.2026.0007', 'name' => 'Salsa Amelia', 'email' => 'salsa.amelia@example.com'],
            ['nim' => 'A11.2026.0008', 'name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@example.com'],
            ['nim' => 'A11.2026.0009', 'name' => 'Intan Maharani', 'email' => 'intan.maharani@example.com'],
            ['nim' => 'A11.2026.0010', 'name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan@example.com'],
        ])->map(fn(array $student) => Student::updateOrCreate(['nim' => $student['nim']], $student));

        $algorithmOfferingA->students()->sync($students->slice(0, 8)->pluck('id')->all());
        $dataStructuresOfferingB->students()->sync($students->slice(4, 6)->pluck('id')->all());
        $algorithmOfferingNext->students()->sync($students->slice(0, 5)->pluck('id')->all());

        Exam::updateOrCreate(
            ['slug' => 'if101-midterm-2026-odd-a'],
            [
                'course_offering_id' => $algorithmOfferingA->id,
                'title' => 'IF101 Midterm Coding Exam - Class A',
                'description' => 'Midterm exam covering control flow, string processing, arrays, and dynamic programming basics.',
                'opens_at' => now()->subDay(),
                'closes_at' => now()->addDays(5),
                'duration_minutes' => 120,
                'status' => 'published',
                'show_score_immediately' => true,
                'max_tab_switches' => 3,
                'tab_switch_warning_count' => 1,
                'inactivity_limit_seconds' => 60,
                'inactivity_warning_seconds' => 15,
                'easy_count' => 2,
                'medium_count' => 2,
                'hard_count' => 1,
                'easy_weight' => 20,
                'medium_weight' => 30,
                'hard_weight' => 50,
            ]
        );

        Exam::updateOrCreate(
            ['slug' => 'if202-practice-2026-odd-b'],
            [
                'course_offering_id' => $dataStructuresOfferingB->id,
                'title' => 'IF202 Practice Exam - Class B',
                'description' => 'Practice coding exam focused on arrays, stacks, and dictionary counting.',
                'opens_at' => now()->addDays(3),
                'closes_at' => now()->addDays(10),
                'duration_minutes' => 90,
                'status' => 'draft',
                'show_score_immediately' => true,
                'max_tab_switches' => 2,
                'tab_switch_warning_count' => 1,
                'inactivity_limit_seconds' => 90,
                'inactivity_warning_seconds' => 20,
                'easy_count' => 1,
                'medium_count' => 1,
                'hard_count' => 1,
                'easy_weight' => 25,
                'medium_weight' => 35,
                'hard_weight' => 40,
            ]
        );

        $this->seedRealQuestions($algorithmOfferingA->id, 'if101-a');
        $this->seedRealQuestions($dataStructuresOfferingB->id, 'if202-b');
    }

    private function seedRealQuestions(int $courseOfferingId, string $prefix): void
    {
        $questions = [
            [
                'slug' => $prefix . '-sum-two-integers',
                'title' => 'Sum Two Integers',
                'difficulty' => 'easy',
                'language' => 'Python (3.8.1)',
                'default_weight' => 10,
                'description' => "Given two integers a and b from standard input, print their sum.\nInput format: one line containing two integers separated by a space.\nOutput format: one integer.",
                'starter_code' => "a, b = map(int, input().split())\n# Write your solution below\n",
                'test_cases' => [
                    ['input' => '3 5', 'expected_output' => "8\n", 'is_hidden' => false],
                    ['input' => '-4 10', 'expected_output' => "6\n", 'is_hidden' => false],
                    ['input' => '12345 67890', 'expected_output' => "80235\n", 'is_hidden' => true],
                ],
            ],
            [
                'slug' => $prefix . '-even-or-odd',
                'title' => 'Even or Odd',
                'difficulty' => 'easy',
                'language' => 'Python (3.8.1)',
                'default_weight' => 10,
                'description' => "Read one integer n and print EVEN if n is even, otherwise print ODD.",
                'starter_code' => "n = int(input())\n# Print EVEN or ODD\n",
                'test_cases' => [
                    ['input' => '8', 'expected_output' => "EVEN\n", 'is_hidden' => false],
                    ['input' => '7', 'expected_output' => "ODD\n", 'is_hidden' => false],
                    ['input' => '0', 'expected_output' => "EVEN\n", 'is_hidden' => true],
                ],
            ],
            [
                'slug' => $prefix . '-count-vowels',
                'title' => 'Count Vowels in a String',
                'difficulty' => 'medium',
                'language' => 'Python (3.8.1)',
                'default_weight' => 15,
                'description' => "Given a lowercase or uppercase string s, count how many vowels appear in it. Vowels are a, e, i, o, u.",
                'starter_code' => "s = input().strip()\n# Print number of vowels\n",
                'test_cases' => [
                    ['input' => 'Informatika', 'expected_output' => "5\n", 'is_hidden' => false],
                    ['input' => 'rhythm', 'expected_output' => "0\n", 'is_hidden' => false],
                    ['input' => 'AEIOUxyz', 'expected_output' => "5\n", 'is_hidden' => true],
                ],
            ],
            [
                'slug' => $prefix . '-rotate-array-right',
                'title' => 'Rotate Array to the Right',
                'difficulty' => 'medium',
                'language' => 'Python (3.8.1)',
                'default_weight' => 15,
                'description' => "Given n and k, and an array of n integers, rotate the array to the right by k positions and print the result separated by spaces.\nInput: first line n k, second line n integers.",
                'starter_code' => "n, k = map(int, input().split())\narr = list(map(int, input().split()))\n# Print rotated array\n",
                'test_cases' => [
                    ['input' => "5 2\n1 2 3 4 5", 'expected_output' => "4 5 1 2 3\n", 'is_hidden' => false],
                    ['input' => "4 4\n9 8 7 6", 'expected_output' => "9 8 7 6\n", 'is_hidden' => false],
                    ['input' => "6 9\n1 1 2 3 5 8", 'expected_output' => "3 5 8 1 1 2\n", 'is_hidden' => true],
                ],
            ],
            [
                'slug' => $prefix . '-longest-increasing-subsequence-length',
                'title' => 'Longest Increasing Subsequence Length',
                'difficulty' => 'hard',
                'language' => 'Python (3.8.1)',
                'default_weight' => 20,
                'description' => "Given an integer n and a sequence of n integers, compute the length of the longest strictly increasing subsequence.",
                'starter_code' => "n = int(input())\narr = list(map(int, input().split()))\n# Print LIS length\n",
                'test_cases' => [
                    ['input' => "8\n10 9 2 5 3 7 101 18", 'expected_output' => "4\n", 'is_hidden' => false],
                    ['input' => "5\n5 4 3 2 1", 'expected_output' => "1\n", 'is_hidden' => false],
                    ['input' => "10\n1 3 2 4 3 5 4 6 5 7", 'expected_output' => "6\n", 'is_hidden' => true],
                ],
            ],
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['slug' => $question['slug']],
                [
                    'course_offering_id' => $courseOfferingId,
                    'title' => $question['title'],
                    'difficulty' => $question['difficulty'],
                    'description' => $question['description'],
                    'default_weight' => $question['default_weight'],
                    'test_cases' => $question['test_cases'],
                    'starter_code' => $question['starter_code'],
                    'language' => $question['language'],
                ]
            );
        }
    }
}
