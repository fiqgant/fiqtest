<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use App\Services\ExamAccessService;
use Illuminate\Http\Request;

class ExamAccessController extends Controller
{
    public function showInstructions(Exam $exam)
    {
        if ($exam->status !== 'published') {
            return view('exam.closed', ['exam' => $exam]);
        }
        
        return view('exam.instructions', ['exam' => $exam]);
    }

    public function verifyNim(Request $request, Exam $exam)
    {
        $request->validate([
            'nim' => 'required|string|max:50',
        ]);
        
        $student = Student::where('nim', $request->nim)->first();
        
        if (!$student) {
            return response()->json([
                'valid' => false,
                'message' => 'NIM not found. Please check your NIM.',
            ]);
        }
        
        $enrolled = $student->courseOfferings()
            ->where('course_offerings.id', $exam->course_offering_id)
            ->exists();
        
        if (!$enrolled) {
            return response()->json([
                'valid' => false,
                'message' => 'You are not enrolled in this course.',
            ]);
        }
        
        $accessService = app(ExamAccessService::class);
        [$canAccess, $error] = $accessService->canAccessExam($exam, $student);
        
        if (!$canAccess) {
            return response()->json([
                'valid' => false,
                'message' => $error,
            ]);
        }
        
        return response()->json([
            'valid' => true,
            'student' => [
                'nim' => $student->nim,
                'name' => $student->name,
            ],
        ]);
    }

    public function startExam(Request $request, Exam $exam)
    {
        $request->validate([
            'nim' => 'required|string',
        ]);
        
        $student = Student::where('nim', $request->nim)->firstOrFail();
        
        $accessService = app(ExamAccessService::class);
        [$canAccess, $error] = $accessService->canAccessExam($exam, $student);
        
        if (!$canAccess) {
            return response()->json([
                'ok' => false,
                'message' => $error,
            ], 422);
        }
        
        $attempt = $accessService->createAttempt($exam, $student);
        
        return response()->json([
            'ok' => true,
            'redirect' => route('exam.workspace', $attempt->id),
        ]);
    }
}
