<?php

namespace Tests\Unit;

use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use App\Services\GradingService;
use App\Services\Judge0Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_grades_question_with_proportional_partial_scoring(): void
    {
        $attemptQuestion = $this->createAttemptQuestion(20, [
            ['input' => '1', 'expected_output' => '10'],
            ['input' => '2', 'expected_output' => '20'],
            ['input' => '3', 'expected_output' => '30'],
            ['input' => '4', 'expected_output' => '40'],
        ]);

        $this->bindJudge0Responses([
            $this->fakeResult('10', 'success'),
            $this->fakeResult('999', 'success'),
            $this->fakeResult('30', 'success'),
            $this->fakeResult(null, 'error'),
        ]);

        app(GradingService::class)->gradeQuestion($attemptQuestion);

        $attemptQuestion->refresh();

        $this->assertSame(2, $attemptQuestion->passed_tests);
        $this->assertSame(4, $attemptQuestion->total_tests);
        $this->assertFalse($attemptQuestion->is_correct);
        $this->assertEquals(10.0, (float) $attemptQuestion->score);
    }

    public function test_it_marks_question_correct_when_all_tests_pass(): void
    {
        $attemptQuestion = $this->createAttemptQuestion(30, [
            ['input' => 'a', 'expected_output' => 'ok'],
            ['input' => 'b', 'expected_output' => 'ok2'],
        ]);

        $this->bindJudge0Responses([
            $this->fakeResult('ok', 'success'),
            $this->fakeResult('ok2', 'success'),
        ]);

        app(GradingService::class)->gradeQuestion($attemptQuestion);

        $attemptQuestion->refresh();

        $this->assertTrue($attemptQuestion->is_correct);
        $this->assertSame(2, $attemptQuestion->passed_tests);
        $this->assertSame(2, $attemptQuestion->total_tests);
        $this->assertEquals(30.0, (float) $attemptQuestion->score);
    }

    public function test_grade_all_questions_updates_attempt_totals_once_all_questions_are_graded(): void
    {
        $attemptQuestionOne = $this->createAttemptQuestion(20, [
            ['input' => '1', 'expected_output' => '1'],
            ['input' => '2', 'expected_output' => '2'],
        ]);

        $attempt = $attemptQuestionOne->attempt;

        AttemptQuestion::create([
            'attempt_id' => $attempt->id,
            'question_id' => Question::create([
                'course_offering_id' => $attempt->exam->course_offering_id,
                'title' => 'Q2',
                'slug' => 'q2',
                'difficulty' => 'easy',
                'description' => 'Question two',
                'default_weight' => 10,
                'test_cases' => [
                    ['input' => 'x', 'expected_output' => 'x'],
                    ['input' => 'y', 'expected_output' => 'y'],
                ],
                'starter_code' => '',
                'language' => 'python',
            ])->id,
            'weight' => 10,
            'max_score' => 10,
            'code' => 'print("x")',
        ]);

        $this->bindJudge0Responses([
            $this->fakeResult('1', 'success'),
            $this->fakeResult('2', 'success'),
            $this->fakeResult('x', 'success'),
            $this->fakeResult('wrong', 'success'),
        ]);

        app(GradingService::class)->gradeAllQuestions($attempt);

        $attempt->refresh();

        $this->assertEquals(25.0, (float) $attempt->total_score);
        $this->assertEquals(30.0, (float) $attempt->max_score);
        $this->assertSame('graded', $attempt->status);
    }

    private function createAttemptQuestion(float $weight, array $testCases): AttemptQuestion
    {
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Odd Semester',
            'slug' => '2026-2027-odd-unit',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $course = Course::create([
            'name' => 'Algorithms',
            'code' => 'IF201',
            'description' => 'Algorithms course',
        ]);

        $offering = CourseOffering::create([
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'class_name' => 'A',
        ]);

        $exam = Exam::create([
            'course_offering_id' => $offering->id,
            'title' => 'Final Exam',
            'slug' => 'final-exam-unit-' . uniqid(),
            'description' => 'Final exam',
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHour(),
            'duration_minutes' => 120,
            'status' => 'published',
            'show_score_immediately' => true,
            'easy_count' => 0,
            'medium_count' => 0,
            'hard_count' => 0,
            'easy_weight' => 20,
            'medium_weight' => 30,
            'hard_weight' => 50,
        ]);

        $student = Student::create([
            'nim' => 'A11.2020.' . random_int(1000, 9999),
            'name' => 'Unit Test Student',
            'email' => 'unit@example.com',
        ]);

        $attempt = Attempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now()->subMinutes(20),
            'status' => 'in_progress',
        ]);

        $question = Question::create([
            'course_offering_id' => $offering->id,
            'title' => 'Q1',
            'slug' => 'q1-' . uniqid(),
            'difficulty' => 'easy',
            'description' => 'Question one',
            'default_weight' => $weight,
            'test_cases' => $testCases,
            'starter_code' => '',
            'language' => 'python',
        ]);

        return AttemptQuestion::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'weight' => $weight,
            'max_score' => $weight,
            'code' => 'print("placeholder")',
        ]);
    }

    private function bindJudge0Responses(array $responses): void
    {
        $fakeJudge0 = new class($responses)
        {
            public function __construct(private array $responses)
            {
            }

            public function execute(...$args)
            {
                if (count($this->responses) === 0) {
                    return new class
                    {
                        public ?string $output = null;

                        public function isSuccess(): bool
                        {
                            return false;
                        }
                    };
                }

                return array_shift($this->responses);
            }
        };

        $this->app->instance(Judge0Service::class, $fakeJudge0);
    }

    private function fakeResult(?string $output, string $status): object
    {
        return new class($output, $status)
        {
            public function __construct(public ?string $output, private string $status)
            {
            }

            public function isSuccess(): bool
            {
                return $this->status === 'success';
            }
        };
    }
}
