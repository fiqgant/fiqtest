<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\BulkQuestionController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Exam\ExamAccessController;
use App\Http\Controllers\Exam\ExamWorkspaceController;
use App\Http\Controllers\Exam\HintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $exams = \App\Models\Exam::with(['courseOffering.course', 'courseOffering.academicPeriod'])
        ->where('status', 'published')
        ->where('opens_at', '<=', now())
        ->where('closes_at', '>=', now())
        ->orderBy('closes_at')
        ->get();

    return view('welcome', compact('exams'));
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('admin.dashboard');

        Route::resource('academic-periods', AcademicPeriodController::class)->except('show')->names('admin.academic-periods');

        Route::get('profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');
        Route::resource('courses', CourseController::class)->except('show')->names('admin.courses');
        Route::resource('course-offerings', CourseOfferingController::class)->except('show')->names('admin.course-offerings');
        Route::get('course-offerings/{courseOffering}/enrollment', [CourseOfferingController::class, 'enrollment'])->name('admin.course-offerings.enrollment');
        Route::post('course-offerings/{courseOffering}/enrollment', [CourseOfferingController::class, 'syncEnrollment'])->name('admin.course-offerings.enrollment.sync');

        Route::resource('students', StudentController::class)->except('show')->names('admin.students');

        Route::resource('exams', ExamController::class)->except('show')->names('admin.exams');
        Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('admin.exams.publish');
        Route::post('exams/{exam}/close', [ExamController::class, 'close'])->name('admin.exams.close');
        Route::get('exams/{exam}/attempts', [ExamController::class, 'attempts'])->name('admin.exams.attempts');
        Route::get('exams/{exam}/attempts/{attempt}', [ExamController::class, 'attemptDetail'])->name('admin.exams.attempts.detail');

        Route::get('questions/bulk/import', [BulkQuestionController::class, 'showImport'])->name('admin.questions.bulk.import');
        Route::get('questions/bulk/template', [BulkQuestionController::class, 'downloadTemplate'])->name('admin.questions.bulk.template');
        Route::post('questions/bulk/preview', [BulkQuestionController::class, 'preview'])->name('admin.questions.bulk.preview');
        Route::post('questions/bulk/import', [BulkQuestionController::class, 'import'])->name('admin.questions.bulk.store');
        Route::resource('questions', QuestionController::class)->except('show')->names('admin.questions');

        Route::get('settings/judge0', [SystemSettingController::class, 'editJudge0'])->name('admin.settings.judge0.edit');
        Route::put('settings/judge0', [SystemSettingController::class, 'updateJudge0'])->name('admin.settings.judge0.update');
        Route::post('settings/judge0/test', [SystemSettingController::class, 'testJudge0'])->name('admin.settings.judge0.test');

        Route::get('reports/offering/{courseOffering}', [ReportController::class, 'offering'])->name('admin.reports.offering');
        Route::get('reports/period/{academicPeriod}', [ReportController::class, 'period'])->name('admin.reports.period');
        Route::get('reports/student/{student}', [ReportController::class, 'student'])->name('admin.reports.student');
        Route::get('reports/offering/{courseOffering}/export', [ReportController::class, 'exportOffering'])->name('admin.reports.offering.export');
        Route::get('reports', [ReportController::class, 'index'])->name('admin.reports.index');
    });
});

Route::prefix('exam/{exam:slug}')->group(function () {
    Route::get('/', [ExamAccessController::class, 'showInstructions'])->name('exam.instructions');
    Route::post('/verify', [ExamAccessController::class, 'verifyNim'])->name('exam.verify');
    Route::post('/start', [ExamAccessController::class, 'startExam'])->name('exam.start');
});

Route::prefix('attempt/{attempt}')->group(function () {
    Route::get('/workspace', [ExamWorkspaceController::class, 'workspace'])->name('exam.workspace');
    Route::post('/run', [ExamWorkspaceController::class, 'runCode'])->name('exam.run');
    Route::post('/autosave', [ExamWorkspaceController::class, 'autosave'])->name('exam.autosave');
    Route::post('/activity', [ExamWorkspaceController::class, 'trackActivity'])->name('exam.activity');
    Route::post('/submit', [ExamWorkspaceController::class, 'finalSubmit'])->name('exam.submit');
    Route::post('/hint/{attemptQuestion}', [HintController::class, 'use'])->name('exam.hint');
    Route::get('/submitted', [ExamWorkspaceController::class, 'submitted'])->name('exam.submitted');
    Route::get('/result', [ExamWorkspaceController::class, 'result'])->name('exam.result');
});

Route::get('/exam', function () {
    return response('Exam endpoint: /exam/{exam-slug}', 200);
});
