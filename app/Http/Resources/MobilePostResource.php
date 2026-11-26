<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobilePostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'author_name' => $this->user?->name ?? 'Alumni member',
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
