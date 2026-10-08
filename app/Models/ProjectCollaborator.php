<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCollaborator extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'role',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
