<?php

namespace Tests\Unit;

use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptTimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_seconds_respects_exam_closing_window(): void
    {
        $period = AcademicPeriod::create([
            'name' => '2026/2027 Odd Semester',
            'slug' => '2026-2027-odd',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $course = Course::create([
            'name' => 'Algorithm Design',
            'code' => 'IF101',
            'description' => 'Intro to algorithmic problem solving.',
        ]);

        $offering = CourseOffering::create([
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'class_name' => 'A',
        ]);

        $exam = Exam::create([
            'course_offering_id' => $offering->id,
            'title' => 'Mid Exam',
            'slug' => 'mid-exam-2026-timer',
            'description' => 'Mid exam for coding class.',
            'opens_at' => now()->subMinutes(30),
            'closes_at' => now()->addMinutes(15),
            'duration_minutes' => 120,
            'status' => 'published',
            'show_score_immediately' => true,
            'max_tab_switches' => 3,
            'inactivity_limit_seconds' => 60,
            'easy_count' => 0,
            'medium_count' => 0,
            'hard_count' => 0,
            'easy_weight' => 20,
            'medium_weight' => 30,
            'hard_weight' => 50,
        ]);

        $student = Student::create([
            'nim' => 'A11.2020.0999',
            'name' => 'Timer Student',
            'email' => 'timer@example.com',
        ]);

        $attempt = Attempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'tab_switch_count' => 0,
            'last_activity_at' => now(),
            'is_disqualified' => false,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $remaining = $attempt->getRemainingSeconds();

        $this->assertLessThanOrEqual(15 * 60, $remaining);
        $this->assertGreaterThan(0, $remaining);
    }
}
