<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function offerings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }
}
