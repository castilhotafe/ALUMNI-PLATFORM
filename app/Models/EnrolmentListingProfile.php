<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrolmentListingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrolment_listing_id',
        'bio',
        'details',
        'picture_url',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function enrolmentListing(): BelongsTo
    {
        return $this->belongsTo(EnrolmentListing::class);
    }
}
