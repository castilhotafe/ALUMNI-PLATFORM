<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'title',
    'description',
    'repo_url',
    'visibility',
])]
class Project extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_projects')
            ->withPivot('contribution_role')
            ->withTimestamps();
    }
}
