<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchPaperAuthor extends Model
{
    protected $fillable = [
        'research_paper_id',
        'name',
        'author_order',
    ];

    public function researchPaper(): BelongsTo
    {
        return $this->belongsTo(ResearchPaper::class);
    }
}
