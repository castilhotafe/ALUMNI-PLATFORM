<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('Admin');
    Role::findOrCreate('General User');
    $this->withoutMiddleware(PreventRequestForgery::class);
});

describe('Admin Contribution Ledger (ContributionController)', function (): void {

    it('denies non-admin users from accessing the contribution ledger', function (): void {
        $user = User::factory()->create();
        $user->assignRole('General User');

        $this->actingAs($user)
            ->get(route('admin.contributions.index'))
            ->assertForbidden();
    });

    it('renders the contribution ledger index with summary metrics and paginated entries', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $recipient = User::factory()->create(['name' => 'Jane Student']);
        $recipient->addContribution(50, 'Initial award', null, $admin);
        $recipient->deductContribution(15, 'Minor penalty', null, $admin);

        $response = $this->actingAs($admin)
            ->get(route('admin.contributions.index'));

        $response->assertOk()
            ->assertViewIs('admin.contributions.index')
            ->assertViewHasAll([
                'pageTitle' => 'Contribution Ledger',
                'sectionKicker' => 'Administration',
            ])
            ->assertViewHas('summary', [
                'net' => 35,
                'awards' => 50,
                'penalties' => -15,
                'entries' => 2,
            ])
            ->assertViewHas('contributions', fn ($contributions) => $contributions->total() === 2)
            ->assertViewHas('users', fn ($users) => $users->contains('id', $recipient->id));
    });

    it('grants contribution points to a user without a reference model', function (): void {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $targetUser = User::factory()->create(['name' => 'Alex Alumni']);

        $response = $this->actingAs($admin)
            ->post(route('admin.contributions.store'), [
                'user_id' => $targetUser->id,
                'amount' => 30,
                'direction' => 'grant',
                'reason' => 'Community mentorship bonus',
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.contributions.index'))
            ->assertSessionHas('status', 'Contribution ledger updated for Alex Alumni.');

        $this->assertDatabaseHas('contributions', [
            'user_id' => $targetUser->id,
            'admin_id' => $admin->id,
            'amount' => 30,
            'reason' => 'Community mentorship bonus',
            'reference_type' => null,
            'reference_id' => null,
        ]);
    });

    it('penalizes contribution points with a polymorphic reference model', function (): void {
        config()->set('contributions.reference_types', [
            'project' => [
                'label' => 'Project',
                'class' => Project::class,
            ],
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $targetUser = User::factory()->create(['name' => 'Sam Member']);
        $project = Project::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('admin.contributions.store'), [
                'user_id' => $targetUser->id,
                'amount' => 20,
                'direction' => 'penalize',
                'reason' => 'Spam project submission',
                'reference_type' => 'project',
                'reference_id' => $project->id,
            ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.contributions.index'))
            ->assertSessionHas('status', 'Contribution ledger updated for Sam Member.');

        $this->assertDatabaseHas('contributions', [
            'user_id' => $targetUser->id,
            'admin_id' => $admin->id,
            'amount' => -20,
            'reason' => 'Spam project submission',
            'reference_type' => $project->getMorphClass(),
            'reference_id' => $project->id,
        ]);
    });

    it('validates required and paired reference fields when storing ledger adjustments', function (): void {
        config()->set('contributions.reference_types', [
            'project' => [
                'label' => 'Project',
                'class' => Project::class,
            ],
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $targetUser = User::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.contributions.index'))
            ->post(route('admin.contributions.store'), [
                'user_id' => $targetUser->id,
                'amount' => 0,
                'direction' => 'invalid',
                'reason' => '',
                'reference_type' => 'project',
            ])
            ->assertRedirect(route('admin.contributions.index'))
            ->assertSessionHasErrors(['amount', 'direction', 'reason', 'reference_id']);
    });
});
