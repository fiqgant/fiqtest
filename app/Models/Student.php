<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = ['nim', 'name', 'email'];

    public function courseOfferings(): BelongsToMany
    {
        return $this->belongsToMany(CourseOffering::class, 'course_offering_students')
            ->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}
