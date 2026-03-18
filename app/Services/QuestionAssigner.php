<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Exam;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

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
            $easy = $exam->questions()->where('difficulty', 'easy')->inRandomOrder()->limit($exam->easy_count)->get();
            $selected = $selected->concat($easy);
        }
        if ($exam->medium_count > 0) {
            $medium = $exam->questions()->where('difficulty', 'medium')->inRandomOrder()->limit($exam->medium_count)->get();
            $selected = $selected->concat($medium);
        }
        if ($exam->hard_count > 0) {
            $hard = $exam->questions()->where('difficulty', 'hard')->inRandomOrder()->limit($exam->hard_count)->get();
            $selected = $selected->concat($hard);
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
