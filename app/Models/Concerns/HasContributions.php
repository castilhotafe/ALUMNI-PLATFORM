<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Contribution;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait HasContributions
{
    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function addContribution(int $amount, string $reason, ?Model $reference = null, ?User $admin = null): Contribution
    {
        if ($amount === 0) {
            throw new InvalidArgumentException('Contribution amount must not be zero.');
        }

        return DB::transaction(function () use ($amount, $reason, $reference, $admin): Contribution {
            return $this->contributions()->create([
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'admin_id' => $admin?->getKey(),
            ]);
        });
    }

    public function deductContribution(int $amount, string $reason, ?Model $reference = null, ?User $admin = null): Contribution
    {
        return $this->addContribution(-abs($amount), $reason, $reference, $admin);
    }

    public function getTotalContributionsAttribute(): int
    {
        if ($this->relationLoaded('contributions')) {
            $contributions = $this->getRelationValue('contributions');

            if (is_iterable($contributions)) {
                return (int) collect($contributions)->sum('amount');
            }
        }

        return (int) $this->contributions()->sum('amount');
    }

    public function getTafeBadgeAttribute(): string
    {
        $totalContributions = $this->getTotalContributionsAttribute();
        $badgeDefinitions = config('contributions.badges') ?? [];

        foreach ($badgeDefinitions as $badgeDefinition) {
            $minimum = (int) ($badgeDefinition['min'] ?? 0);
            $maximum = $badgeDefinition['max'] ?? null;

            if ($totalContributions < $minimum) {
                continue;
            }

            if ($maximum !== null && $totalContributions > (int) $maximum) {
                continue;
            }

            return (string) ($badgeDefinition['label'] ?? 'First-Year');
        }

        return 'First-Year';
    }
}
