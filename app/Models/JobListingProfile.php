<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobListingProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_listing_id',
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

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
