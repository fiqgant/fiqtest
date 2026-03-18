<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\AttemptQuestion;
use App\Models\Exam;
use App\Models\Question;
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
        $filterTags = $exam->question_filter_tags ?? [];
        $selected = collect();

        foreach (['easy', 'medium', 'hard'] as $diff) {
            $countKey = "{$diff}_count";
            $count = (int) $exam->$countKey;
            if ($count <= 0) continue;

            $query = Question::where('course_offering_id', $exam->course_offering_id)
                ->where('difficulty', $diff);

            if (!empty($filterTags)) {
                $query->whereHas('tags', fn($q) => $q->whereIn('question_tags.id', $filterTags));
            }

            $selected = $selected->concat($query->inRandomOrder()->limit($count)->get());
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
