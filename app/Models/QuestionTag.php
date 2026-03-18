<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    protected $fillable = ['name'];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_tag', 'question_tag_id', 'question_id');
    }
}
