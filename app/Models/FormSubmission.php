<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'session_id',
        'project_code',
        'form_id',
        'form_name',
        'page_url',
        'form_data'
    ];

    protected $table = 'form_submissions';


    // If you're using JSON columns, make sure to cast them appropriately
    protected $casts = [
        'form_data' => 'array'
    ];

    // If you have relationships, define them here
    // For example, if each form submission is related to a project
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
