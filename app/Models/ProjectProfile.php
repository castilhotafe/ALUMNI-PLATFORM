<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProfile extends Model
{
    protected $fillable = [
        'project_id',
        'bio',
        'details',
        'picture_url',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
