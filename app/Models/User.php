<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
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
            'password' => 'hashed',
            'student_id' => 'integer',
            'isCurrent' => 'boolean',
            'isAlumni' => 'boolean',
            'isLecturer' => 'boolean',
            'isPartner' => 'boolean',
            'isVerifiedCurrent' => 'boolean',
            'isVerifiedAlumni' => 'boolean',
            'isVerifiedLecturer' => 'boolean',
            'isVerifiedPartner' => 'boolean',
        ];
    }
}
