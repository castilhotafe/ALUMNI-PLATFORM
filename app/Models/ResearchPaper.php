<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ResearchPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'abstract',
        'doi',
        'pdf_url',
        'visibility',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(ResearchPaperProfile::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_papers')->withPivot('author_order')->withTimestamps();
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ResearchPaperAuthor::class);
    }

    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // Condition 1: Always show public posts
            $q->where('visibility', strtolower('public'));

            // Condition 2: If the user is logged in, also show their private posts
            if ($user) {
                $q->orWhere(function ($innerQuery) use ($user) {
                    $innerQuery->where('visibility', strtolower('private'))
                        ->whereHas('users', fn ($userQuery) => $userQuery->whereKey($user->id));
                });
            }
        });
    }
}
