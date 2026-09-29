<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'title',
    'description',
    'repo_url',
    'demo_url',
    'visibility',
])]
class Project extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('author_order')
            ->orderByPivot('author_order');
    }

    public function primaryAuthor(): BelongsToMany
    {
        return $this->users()->wherePivot('author_order', 1);
    }
}
