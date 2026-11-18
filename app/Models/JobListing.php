<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_user_id',
        'title',
        'company',
        'description',
        'apply_link',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(JobListingProfile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }
}
