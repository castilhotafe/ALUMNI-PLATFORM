<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupProfile extends Model
{
    protected $fillable = [
        'group_id',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
