<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'goal_name',
        'goal_type',
        'goal_value',
        'goal_description',
        'goal_uuid',
    ];

    protected static function booted()
    {
        static::creating(function ($goal) {
            $goal->goal_uuid = Str::uuid();
        });
    }

    // need to set the goal to belong to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
