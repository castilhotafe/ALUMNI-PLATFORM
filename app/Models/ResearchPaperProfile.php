<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchPaperProfile extends Model
{
    protected $fillable = [
        'research_paper_id',
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

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }
}
