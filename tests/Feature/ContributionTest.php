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
