<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EnrolmentListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_user_id',
        'course_title',
        'tafe_provider',
        'description',
        'enrol_link',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(EnrolmentListingProfile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }
}
