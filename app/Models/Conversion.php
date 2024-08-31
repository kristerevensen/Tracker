<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    protected $table = 'conversions';

    protected $fillable = [
        'session_id', 'project_code', 'conversion_type', 'conversion_value',
        'page_url', 'referrer', 'timestamp'
    ];

    public function project() {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
