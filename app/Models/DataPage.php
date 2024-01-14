<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPage extends Model
{
    use HasFactory;

    protected $table = 'data_pages';

     // Columns that are mass assignable
     protected $fillable = [
        'url', 'url_code', 'event_type', 'title', 'referrer', 'device_type',
        'entrance', 'session_start', 'project_code', 'session_id', 'hostname',
        'protocol', 'pathname', 'bounce', 'language', 'meta_description',
        'cookie_enabled', 'screen_width', 'screen_height', 'history_length',
        'word_count', 'form_count', 'inbound_links', 'outbound_links',
        'owner_id'
    ];

     // For example, if 'owner_id' refers to a User model
     public function project() {
        return $this->belongsTo(Project::class, 'project_data', 'project_code');
    }
}
