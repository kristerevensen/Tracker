<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLinkClicks extends Model
{
    use HasFactory;

    protected $fillable = [
        'link_url', 'link_text', 'click_class', 'click_id',
        'data_attributes', 'page_url', 'click_type',
        'coordinates_x', 'coordinates_y', 'session_id', 'project_code'
    ];

    /**
     * Get the project that the link click belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_code', 'project_code');
    }
}
