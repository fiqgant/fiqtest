<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Student;
use Illuminate\Support\Collection;

class GradeReportService
{
    public function getOfferingGrades(CourseOffering $offering): Collection
    {
        $exams = $offering->exams()->get();
        $examIds = $exams->pluck('id');
        
        $students = $offering->students()
            ->with(['attempts' => function ($q) use ($examIds) {
                $q->whereIn('exam_id', $examIds)
                    ->whereIn('status', ['submitted', 'graded']);
            }])
            ->get();
        
        return $students->map(function ($student) use ($exams) {
            $examResults = [];
            $totalScore = 0;
            $totalMax = 0;
            
            foreach ($exams as $exam) {
                $attempt = $student->attempts->firstWhere('exam_id', $exam->id);
                
                $score = $attempt?->total_score ?? 0;
                $max = $attempt?->max_score ?? 0;
                
                // Get attempt questions with question details
                $questions = [];
                if ($attempt) {
                    $attemptQuestions = $attempt->attemptQuestions()
                        ->with('question')
                        ->get();
                    
                    foreach ($attemptQuestions as $aq) {
                        $question = $aq->question;
                        if ($question) {
                            $testCases = $question->test_cases ?? [];
                            $visibleTestCases = array_filter($testCases, fn($tc) => !($tc['is_hidden'] ?? false));
                            
                            $questions[] = [
                                'question_id' => $question->id,
                                'title' => $question->title,
                                'difficulty' => $question->difficulty,
                                'language' => $question->language,
                                'description' => $question->description,
                                'starter_code' => $question->starter_code ?? '',
                                'reference_solution' => $question->reference_solution ?? '',
                                'hint' => $question->hint ?? '',
                                'hint_used_count' => (int) ($aq->hint_used_count ?? 0),
                                'weight' => $aq->weight,
                                'student_code' => $aq->code ?? '',
                                'passed_tests' => $aq->passed_tests ?? 0,
                                'total_tests' => $aq->total_tests ?? 0,
                                'score' => $aq->score ?? 0,
                                'is_correct' => $aq->is_correct ?? false,
                                'test_cases' => array_map(function($tc) {
                                    return [
                                        'input' => $tc['input'] ?? '',
                                        'expected_output' => $tc['expected_output'] ?? '',
                                        'is_hidden' => $tc['is_hidden'] ?? false,
                                    ];
                                }, $testCases),
                                'visible_test_cases' => array_map(function($tc) {
                                    return [
                                        'input' => $tc['input'] ?? '',
                                        'expected_output' => $tc['expected_output'] ?? '',
                                    ];
                                }, $visibleTestCases),
                            ];
                        }
                    }
                }
                
                $examResults[] = [
                    'exam_id' => $exam->id,
                    'exam_title' => $exam->title,
                    'attempt_id' => $attempt?->id,
                    'status' => $attempt ? $attempt->status : 'not_started',
                    'score' => $score,
                    'max_score' => $max,
                    'percentage' => $max > 0 ? round(($score / $max) * 100, 2) : 0,
                    'submitted_at' => $attempt?->submitted_at?->format('Y-m-d H:i'),
                    'is_disqualified' => (bool) ($attempt?->is_disqualified ?? false),
                    'disqualification_reason' => $attempt?->disqualification_reason,
                    'questions' => $questions,
                ];
                
                $totalScore += $score;
                $totalMax += $max;
            }
            
            return [
                'student' => [
                    'id' => $student->id,
                    'nim' => $student->nim,
                    'name' => $student->name,
                ],
                'exams' => $examResults,
                'total_score' => $totalScore,
                'total_max' => $totalMax,
                'overall_average' => $totalMax > 0 ? round(($totalScore / $totalMax) * 100, 2) : 0,
            ];
        })->sortBy('student.nim')->values();
    }

    public function getPeriodGrades(AcademicPeriod $period): Collection
    {
        $offerings = $period->courseOfferings()
            ->with(['course', 'exams', 'students'])
            ->get();
        
        $result = [];
        
        foreach ($offerings as $offering) {
            $offeringGrades = $this->getOfferingGrades($offering);
            
            $studentCount = $offeringGrades->count();
            $submittedCount = $offeringGrades->filter(function ($g) {
                foreach ($g['exams'] as $exam) {
                    if (in_array($exam['status'], ['submitted', 'graded'], true)) {
                        return true;
                    }
                }

                return false;
            })->count();
            
            $avgScore = $offeringGrades->avg('overall_average') ?? 0;
            
            $result[] = [
                'offering' => [
                    'id' => $offering->id,
                    'course' => $offering->course->name,
                    'class' => $offering->class_name,
                ],
                'students' => $offeringGrades,
                'statistics' => [
                    'total_students' => $studentCount,
                    'submitted' => $submittedCount,
                    'not_started' => $studentCount - $submittedCount,
                    'average_score' => round($avgScore, 2),
                ],
            ];
        }
        
        return new Collection($result);
    }

    public function getStudentHistory(Student $student): Collection
    {
        $attempts = $student->attempts()
            ->with(['exam.courseOffering.course', 'exam.courseOffering.academicPeriod'])
            ->whereIn('status', ['submitted', 'graded'])
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        return $attempts->map(function ($attempt) {
            return [
                'id' => $attempt->id,
                'exam' => [
                    'id' => $attempt->exam->id,
                    'title' => $attempt->exam->title,
                    'course' => $attempt->exam->courseOffering->course->name,
                    'period' => $attempt->exam->courseOffering->academicPeriod->name,
                    'class' => $attempt->exam->courseOffering->class_name,
                ],
                'score' => $attempt->total_score,
                'max_score' => $attempt->max_score,
                'percentage' => $attempt->percentage,
                'status' => $attempt->status,
                'is_disqualified' => (bool) $attempt->is_disqualified,
                'disqualification_reason' => $attempt->disqualification_reason,
                'submitted_at' => $attempt->submitted_at
                    ? $attempt->submitted_at->format('Y-m-d H:i')
                    : ($attempt->started_at ? $attempt->started_at->format('Y-m-d H:i') : '-'),
            ];
        });
    }

    public function exportToCsv(CourseOffering $offering): string
    {
        $grades = $this->getOfferingGrades($offering);
        
        $csv = "NIM,Name," . $offering->exams()->pluck('title')->implode(',') . ",Total,Average\n";
        
        foreach ($grades as $grade) {
            $csv .= $grade['student']['nim'] . ",";
            $csv .= $grade['student']['name'] . ",";
            
            foreach ($grade['exams'] as $exam) {
                $csv .= $exam['score'] . "/" . $exam['max_score'] . ",";
            }
            
            $csv .= $grade['total_score'] . "/" . $grade['total_max'] . ",";
            $csv .= $grade['overall_average'] . "\n";
        }
        
        return $csv;
    }
}
