<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class QuestionAssigner
{
    public function assign(Attempt $attempt): void
    {
        $exam = $attempt->exam;

        $questions = $this->selectRandomQuestions($exam);

        if ($questions->count() !== $exam->getTotalQuestionsNeeded()) {
            throw ValidationException::withMessages([
                'question_bank' => 'Question bank does not satisfy this exam distribution.',
            ]);
        }
        
        foreach ($questions as $question) {
            $weight = $this->getWeightForDifficulty($exam, $question->difficulty);
            
            AttemptQuestion::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'weight' => $weight,
                'max_score' => $question->default_weight,
                'code' => $question->starter_code,
            ]);
        }
    }

    private function selectRandomQuestions(Exam $exam): Collection
    {
        $selected = collect();
        
        if ($exam->easy_count > 0) {
            $easyQuestions = Question::where('course_offering_id', $exam->course_offering_id)
                ->where('difficulty', 'easy')
                ->inRandomOrder()
                ->limit($exam->easy_count)
                ->get();
            $selected = $selected->concat($easyQuestions);
        }
        
        if ($exam->medium_count > 0) {
            $mediumQuestions = Question::where('course_offering_id', $exam->course_offering_id)
                ->where('difficulty', 'medium')
                ->inRandomOrder()
                ->limit($exam->medium_count)
                ->get();
            $selected = $selected->concat($mediumQuestions);
        }
        
        if ($exam->hard_count > 0) {
            $hardQuestions = Question::where('course_offering_id', $exam->course_offering_id)
                ->where('difficulty', 'hard')
                ->inRandomOrder()
                ->limit($exam->hard_count)
                ->get();
            $selected = $selected->concat($hardQuestions);
        }
        
        return $selected;
    }

    private function getWeightForDifficulty(Exam $exam, string $difficulty): float
    {
        return match($difficulty) {
            'easy' => (float) $exam->easy_weight,
            'medium' => (float) $exam->medium_weight,
            'hard' => (float) $exam->hard_weight,
            default => 10.0,
        };
    }
}
