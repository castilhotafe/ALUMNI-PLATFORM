<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'password_confirmed_at',
    'email_verified_at',
    'student_id',
    'isCurrent',
    'isAlumni',
    'isLecturer',
    'isPartner',
    'isVerifiedCurrent',
    'isVerifiedAlumni',
    'isVerifiedLecturer',
    'isVerifiedPartner',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, Notifiable;

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'user_projects')->withPivot('contribution_role')->withTimestamps();
    }

    public function researchPapers(): BelongsToMany
    {
        return $this->belongsToMany(ResearchPaper::class, 'user_papers')->withPivot('author_order')->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')->withPivot('member_role')->withTimestamps();
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class, 'creator_user_id');
    }

    public function enrolmentListings(): HasMany
    {
        return $this->hasMany(EnrolmentListing::class, 'creator_user_id');
    }

    public function selfDeclaredRoleLabel(): ?string
    {
        if ($this->isCurrent) {
            return 'Current Student';
        }

        if ($this->isAlumni) {
            return 'Alumni';
        }

        if ($this->isLecturer) {
            return 'Lecturer';
        }

        if ($this->isPartner) {
            return 'Partner';
        }

        if ($this->hasRole('General User')) {
            return 'General User';
        }

        return null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_confirmed_at' => 'datetime',
            'student_id' => 'integer',
            'isCurrent' => 'boolean',
            'isAlumni' => 'boolean',
            'isLecturer' => 'boolean',
            'isPartner' => 'boolean',
            'isVerifiedCurrent' => 'boolean',
            'isVerifiedAlumni' => 'boolean',
            'isVerifiedLecturer' => 'boolean',
            'isVerifiedPartner' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
