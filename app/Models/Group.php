<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'creator_user_id',
    ];

    public function casts(): array
    {
        return [
            'creator_user_id' => 'integer',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(GroupProfile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')->withPivot('member_role')->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'group_projects')->withTimestamps();
    }

    public function researchPapers(): BelongsToMany
    {
        return $this->belongsToMany(ResearchPaper::class, 'group_research_papers')->withTimestamps();
    }
}
