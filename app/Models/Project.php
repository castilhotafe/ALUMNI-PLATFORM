<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'repo_url',
        'visibility',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(ProjectProfile::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_projects')
            ->withPivot('contribution_role')
            ->withTimestamps();
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class);
    }

    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', strtolower('public'));

            if ($user) {
                $q->orWhere(function ($innerQuery) use ($user) {
                    $innerQuery->where('visibility', strtolower('private'))
                        ->whereHas('users', fn ($userQuery) => $userQuery->whereKey($user->id));
                });
            }
        });
    }
}
