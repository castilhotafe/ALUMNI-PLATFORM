<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ContributionTriggered;
use Illuminate\Support\Str;

class RecordContribution
{
    public function handle(ContributionTriggered $event): void
    {
        $definition = config('contributions.events.'.$event->eventKey);

        if (! is_array($definition)) {
            return;
        }

        $points = (int) ($definition['points'] ?? 0);

        if ($points === 0) {
            return;
        }

        $reason = (string) ($definition['reason'] ?? Str::headline(str_replace('_', ' ', $event->eventKey)));

        if ($points < 0) {
            // deductContribution helper handles abs() conversion automatically
            $event->user->deductContribution($points, $reason, $event->reference, $event->admin);
        } else {
            $event->user->addContribution($points, $reason, $event->reference, $event->admin);
        }
    }
}
