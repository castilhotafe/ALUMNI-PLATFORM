<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'repo_url',
        'visibility',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_projects')
            ->withPivot('contribution_role')
            ->withTimestamps();
    }
}
