<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Admin;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\CourseOffering;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExamAccessFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_protected_routes_redirect_to_login_without_session(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_and_access_dashboard(): void
    {
        Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));

        $dashboardResponse = $this->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();

        $reportsResponse = $this->get(route('admin.reports.index'));
        $reportsResponse->assertOk();
    }

    public function test_admin_can_open_offering_report_page_without_blade_parse_error(): void
    {
        Admin::create([
            'name' => 'Main Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        [, $offering] = $this->createPublishedExamWithOffering();

        $response = $this->get(route('admin.reports.offering', ['courseOffering' => $offering->id]));
        $response->assertOk();
    }

    public function test_verify_nim_returns_invalid_for_unknown_student(): void
    {
        $exam = $this->createPublishedExam();

        $response = $this->postJson(route('exam.verify', $exam), [
            'nim' => 'UNKNOWN123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'valid' => false,
                'message' => 'NIM not found. Please check your NIM.',
            ]);
    }

    public function test_verify_nim_returns_valid_for_enrolled_student(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();

        $student = Student::create([
            'nim' => 'A11.2020.0001',
            'name' => 'Student One',
            'email' => 'student1@example.com',
        ]);
        $offering->students()->attach($student->id);

        $response = $this->postJson(route('exam.verify', $exam), [
            'nim' => $student->nim,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('student.nim', 'A11.2020.0001')
            ->assertJsonPath('student.name', 'Student One');
    }

    public function test_start_exam_creates_attempt_and_returns_workspace_redirect(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();

        $student = Student::create([
            'nim' => 'A11.2020.0002',
            'name' => 'Student Two',
            'email' => 'student2@example.com',
        ]);
        $offering->students()->attach($student->id);

        $response = $this->postJson(route('exam.start', $exam), [
            'nim' => $student->nim,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['ok', 'redirect']);

        $this->assertDatabaseHas('attempts', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_verify_nim_rejects_when_exam_is_outside_time_window_even_if_published(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();
        $exam->update([
            'opens_at' => now()->subHours(3),
            'closes_at' => now()->subHour(),
            'status' => 'published',
        ]);

        $student = Student::create([
            'nim' => 'A11.2020.0011',
            'name' => 'Student Eleven',
            'email' => 'student11@example.com',
        ]);
        $offering->students()->attach($student->id);

        $response = $this->postJson(route('exam.verify', $exam), [
            'nim' => $student->nim,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Exam has already closed. Closed at: ' . $exam->closes_at->format('Y-m-d H:i'));
    }

    public function test_verify_nim_rejects_when_exam_not_started_even_if_published(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();
        $exam->update([
            'opens_at' => now()->addHours(1),
            'closes_at' => now()->addHours(3),
            'status' => 'published',
        ]);

        $student = Student::create([
            'nim' => 'A11.2020.0013',
            'name' => 'Student Thirteen',
            'email' => 'student13@example.com',
        ]);
        $offering->students()->attach($student->id);

        $response = $this->postJson(route('exam.verify', $exam), [
            'nim' => $student->nim,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('message', 'Exam has not opened yet. Opens at: ' . $exam->opens_at->format('Y-m-d H:i'));
    }

    public function test_student_cannot_start_exam_second_time_after_submission(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();

        $student = Student::create([
            'nim' => 'A11.2020.0012',
            'name' => 'Student Twelve',
            'email' => 'student12@example.com',
        ]);
        $offering->students()->attach($student->id);

        $this->postJson(route('exam.start', $exam), [
            'nim' => $student->nim,
        ])->assertOk();

        $attempt = Attempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->postJson(route('exam.start', $exam), [
            'nim' => $student->nim,
        ])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('message', 'You have already submitted this exam.');
    }

    public function test_attempt_is_disqualified_after_exceeding_tab_switch_limit(): void
    {
        [$exam, $offering] = $this->createPublishedExamWithOffering();
        $exam->update([
            'max_tab_switches' => 2,
            'inactivity_limit_seconds' => 60,
        ]);

        $student = Student::create([
            'nim' => 'A11.2020.0003',
            'name' => 'Student Three',
            'email' => 'student3@example.com',
        ]);
        $offering->students()->attach($student->id);

        $this->postJson(route('exam.start', $exam), [
            'nim' => $student->nim,
        ])->assertOk();

        $attempt = Attempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->postJson(route('exam.activity', $attempt), [
            'event' => 'tab_switch',
        ])->assertOk();

        $this->postJson(route('exam.activity', $attempt), [
            'event' => 'tab_switch',
        ])->assertOk()->assertJsonPath('disqualified', true);

        $this->assertDatabaseHas('attempts', [
            'id' => $attempt->id,
            'status' => 'submitted',
            'is_disqualified' => true,
            'tab_switch_count' => 2,
        ]);
    }

    private function createPublishedExam(): Exam
    {
        [$exam] = $this->createPublishedExamWithOffering();

        return $exam;
    }

    private function createPublishedExamWithOffering(): array
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
            'easy_count' => 0,
            'medium_count' => 0,
            'hard_count' => 0,
            'easy_weight' => 20,
            'medium_weight' => 30,
            'hard_weight' => 50,
        ]);

        return [$exam, $offering];
    }
}
