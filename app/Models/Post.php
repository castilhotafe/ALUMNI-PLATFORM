<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'visibility',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // Condition 1: Always show public posts
            $q->where('visibility', strtolower('public'));

            // Condition 2: If the user is logged in, also show their private posts
            if ($user) {
                $q->orWhere(function ($innerQuery) use ($user) {
                    $innerQuery->where('visibility', strtolower('private'))
                        ->where('user_id', $user->id);
                });
            }
        });
    }
}
