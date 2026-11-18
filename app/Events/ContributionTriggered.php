<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionTriggered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $eventKey,
        public readonly ?Model $reference = null,
        public readonly ?User $admin = null,
    ) {}
}
