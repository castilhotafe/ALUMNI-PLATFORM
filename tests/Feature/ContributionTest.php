<?php

use App\Models\Contribution;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Contribution Model Attributes, Casts & Relationships', function () {

    it('casts amount, reference_id, and admin_id attributes to integers', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $contribution = Contribution::create([
            'user_id' => $user->id,
            'amount' => '50',
            'reason' => 'Project created contribution bonus',
            'reference_type' => Project::class,
            'reference_id' => '10',
            'admin_id' => (string) $admin->id,
        ]);

        expect($contribution->amount)->toBeInt()->toBe(50)
            ->and($contribution->reference_id)->toBeInt()->toBe(10)
            ->and($contribution->admin_id)->toBeInt()->toBe($admin->id);
    });

    it('belongs to a recipient user', function () {
        $user = User::factory()->create();
        $contribution = Contribution::factory()->create([
            'user_id' => $user->id,
        ]);

        expect($contribution->user)->toBeInstanceOf(User::class)
            ->and($contribution->user->id)->toBe($user->id);
    });

    it('belongs to an admin user who granted points', function () {
        $admin = User::factory()->create();
        $contribution = Contribution::factory()->create([
            'admin_id' => $admin->id,
        ]);

        expect($contribution->admin)->toBeInstanceOf(User::class)
            ->and($contribution->admin->id)->toBe($admin->id);
    });

    it('morphs to a reference model instance dynamically', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $contribution = Contribution::create([
            'user_id' => $user->id,
            'amount' => 20,
            'reason' => 'Project Created',
            'reference_type' => Project::class,
            'reference_id' => $project->id,
        ]);

        expect($contribution->reference)->toBeInstanceOf(Project::class)
            ->and($contribution->reference->id)->toBe($project->id);
    });
});

describe('HasContributions Trait Methods & Accessors', function () {

    it('retrieves all contributions belonging to a user via the contributions relationship', function () {
        $user = User::factory()->create();
        Contribution::factory()->count(3)->create(['user_id' => $user->id]);

        expect($user->contributions)->toHaveCount(3)
            ->each->toBeInstanceOf(Contribution::class);
    });

    it('adds a positive contribution with optional reference model and admin user', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $project = Project::factory()->create();

        $contribution = $user->addContribution(25, 'Featured project bonus', $project, $admin);

        expect($contribution)->toBeInstanceOf(Contribution::class)
            ->and($contribution->user_id)->toBe($user->id)
            ->and($contribution->amount)->toBe(25)
            ->and($contribution->reason)->toBe('Featured project bonus')
            ->and($contribution->reference_type)->toBe($project->getMorphClass())
            ->and($contribution->reference_id)->toBe($project->getKey())
            ->and($contribution->admin_id)->toBe($admin->id);

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'user_id' => $user->id,
            'amount' => 25,
            'reason' => 'Featured project bonus',
        ]);
    });

    it('throws an InvalidArgumentException when adding a zero-point contribution', function () {
        $user = User::factory()->create();

        expect(fn() => $user->addContribution(0, 'Zero points test'))
            ->toThrow(InvalidArgumentException::class, 'Contribution amount must not be zero.');
    });

    it('deducts points as a negative contribution regardless of input sign', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        // Passing positive 15 should still store -15 via -abs($amount)
        $deductionPositiveArg = $user->deductContribution(15, 'Deleted project', $project);
        // Passing negative -10 should also store -10
        $deductionNegativeArg = $user->deductContribution(-10, 'Admin penalty');

        expect($deductionPositiveArg->amount)->toBe(-15)
            ->and($deductionPositiveArg->reference_id)->toBe($project->id)
            ->and($deductionNegativeArg->amount)->toBe(-10)
            ->and($deductionNegativeArg->reference_id)->toBeNull();
    });

    it('calculates total_contributions via database query when relationship is not loaded', function () {
        $user = User::factory()->create();
        $user->addContribution(30, 'Initial bonus');
        $user->deductContribution(10, 'Minor deduction');

        $freshUser = User::find($user->id);

        expect($freshUser->relationLoaded('contributions'))->toBeFalse()
            ->and($freshUser->total_contributions)->toBe(20);
    });

    it('calculates total_contributions in-memory when contributions relationship is eager-loaded', function () {
        $user = User::factory()->create();
        $user->addContribution(40, 'First award');
        $user->addContribution(15, 'Second award');

        $loadedUser = User::with('contributions')->find($user->id);

        expect($loadedUser->relationLoaded('contributions'))->toBeTrue()
            ->and($loadedUser->total_contributions)->toBe(55);
    });

    it('resolves the matching tafe_badge across minimum, maximum, and fallback boundaries', function () {
        config()->set('contributions.badges', [
            ['min' => 0, 'max' => 49, 'label' => 'First-Year'],
            ['min' => 50, 'max' => 149, 'label' => 'Active Contributor'],
            ['min' => 150, 'max' => null, 'label' => 'Master Alumnus'],
        ]);

        $user = User::factory()->create();

        // 0 points -> hits first tier (0..49)
        expect($user->tafe_badge)->toBe('First-Year');

        // 50 points -> skips 0..49 (exceeds max), matches 50..149
        $user->addContribution(50, 'Tier 2 threshold');
        expect($user->fresh()->tafe_badge)->toBe('Active Contributor');

        // 200 points -> skips 0..49 and 50..149, matches unbounded max (null)
        $user->addContribution(150, 'Tier 3 threshold');
        expect($user->fresh()->tafe_badge)->toBe('Master Alumnus');

        // Negative points (-10) -> skips all tiers (< min) and hits final fallback return
        $user->deductContribution(210, 'Negative balance fallback');
        expect($user->fresh()->tafe_badge)->toBe('First-Year');
    });

    it('falls back to First-Year badge when badge definitions are empty', function () {
        config()->set('contributions.badges', []);
        $user = User::factory()->create();

        expect($user->tafe_badge)->toBe('First-Year');
    });
});

describe('RecordContribution Listener', function () {

    it('ignores unregistered event keys without creating a contribution record', function () {
        config()->set('contributions.events', []);
        $user = User::factory()->create();

        \App\Events\ContributionTriggered::dispatch($user, 'unregistered_event_key');

        expect($user->contributions()->count())->toBe(0);
    });

    it('ignores events configured with zero points', function () {
        config()->set('contributions.events.zero_point_event', [
            'points' => 0,
            'reason' => 'No points awarded',
        ]);
        $user = User::factory()->create();

        \App\Events\ContributionTriggered::dispatch($user, 'zero_point_event');

        expect($user->contributions()->count())->toBe(0);
    });

    it('deducts points when an event is configured with negative points', function () {
        config()->set('contributions.events.penalty_event', [
            'points' => -15,
            'reason' => 'Content removed',
        ]);
        $user = User::factory()->create();

        \App\Events\ContributionTriggered::dispatch($user, 'penalty_event');

        $this->assertDatabaseHas('contributions', [
            'user_id' => $user->id,
            'amount' => -15,
            'reason' => 'Content removed',
        ]);
    });

    it('awards points and generates a fallback reason when reason is omitted', function () {
        config()->set('contributions.events.custom_milestone_reached', [
            'points' => 25,
        ]);
        $user = User::factory()->create();
        $project = Project::factory()->create();

        \App\Events\ContributionTriggered::dispatch($user, 'custom_milestone_reached', $project);

        $this->assertDatabaseHas('contributions', [
            'user_id' => $user->id,
            'amount' => 25,
            'reason' => 'Custom Milestone Reached',
            'reference_type' => $project->getMorphClass(),
            'reference_id' => $project->id,
        ]);
    });
});
