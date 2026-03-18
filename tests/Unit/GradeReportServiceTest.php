<?php

namespace Tests\Unit;

use App\Models\AcademicPeriod;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Student;
use App\Services\GradeReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_history_includes_submitted_attempts(): void
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
            'slug' => 'mid-exam-2026',
            'description' => 'Mid exam for coding class.',
            'opens_at' => now()->subMinutes(30),
            'closes_at' => now()->addMinutes(60),
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
            'nim' => 'A11.2020.0001',
            'name' => 'Student One',
            'email' => 'student1@example.com',
        ]);

        Attempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(5),
            'status' => 'submitted',
            'is_disqualified' => true,
            'disqualification_reason' => 'Disqualified for tab switching.',
            'total_score' => 0,
            'max_score' => 100,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $history = app(GradeReportService::class)->getStudentHistory($student);

        $this->assertCount(1, $history);
        $this->assertSame('submitted', $history->first()['status']);
        $this->assertTrue($history->first()['is_disqualified']);
    }
}
