<?php

use App\Models\Group;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user before each test runs
    $this->user = User::factory()->create(['name' => 'John Doe']);
    $this->actingAs($this->user);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

describe('Group Controller Workflows', function () {

    describe('Store Group Action', function () {

        it('allows an authenticated user to create a group with members, projects, papers, and profile details', function () {
            $user = User::factory()->create();
            $memberUser = User::factory()->create();
            $project = Project::factory()->create();
            $paper = ResearchPaper::factory()->create();

            $payload = [
                'name' => 'Alumni Software Circle',
                'description' => 'A community hub for software developers and designers.',
                'profile_bio' => 'Empowering tech alumni through collaborative innovation.',
                'profile_details' => ['Laravel', 'Vue.js', 'Perth Hub'],
                'picture_url' => 'https://example.com/group-cover.jpg',
                'member_ids' => [$memberUser->id],
                'project_ids' => [$project->id],
                'paper_ids' => [$paper->id],
            ];

            actingAs($user)
                ->from('/networking')
                ->post(route('groups.store'), $payload)
                ->assertRedirect('/networking')
                ->assertSessionHas('status_group', 'Group created.');

            // Verify basic group insertion
            assertDatabaseHas('groups', [
                'name' => 'Alumni Software Circle',
                'description' => 'A community hub for software developers and designers.',
                'creator_user_id' => $user->id,
            ]);

            $group = Group::where('name', 'Alumni Software Circle')->first();

            // Assert member relationship & pivot table roles
            expect($group->members)->toHaveCount(2);

            assertDatabaseHas('group_members', [
                'group_id' => $group->id,
                'user_id' => $user->id,
                'member_role' => 'owner',
            ]);

            assertDatabaseHas('group_members', [
                'group_id' => $group->id,
                'user_id' => $memberUser->id,
                'member_role' => 'member',
            ]);

            // Assert pivot syncs for projects and papers
            assertDatabaseHas('group_projects', [
                'group_id' => $group->id,
                'project_id' => $project->id,
            ]);

            assertDatabaseHas('group_research_papers', [
                'group_id' => $group->id,
                'research_paper_id' => $paper->id,
            ]);

            // Assert group profile record creation
            assertDatabaseHas('group_profiles', [
                'group_id' => $group->id,
                'bio' => 'Empowering tech alumni through collaborative innovation.',
                'picture_url' => 'https://example.com/group-cover.jpg',
            ]);

            expect($group->profile->details)->toBe(['Laravel', 'Vue.js', 'Perth Hub']);
        });

        it('creates a group without a profile if optional profile parameters are omitted', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post(route('groups.store'), [
                    'name' => 'Lean Community Group',
                    'description' => 'A basic group without bio or cover photo.',
                ])
                ->assertSessionHas('status_group', 'Group created.');

            $group = Group::where('name', 'Lean Community Group')->first();
            expect($group->profile)->toBeNull();
        });

        it('redirects with validation error bag when required fields are missing', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->from('/networking')
                ->post(route('groups.store'), [])
                ->assertRedirect('/networking')
                ->assertSessionHasErrors(['name', 'description'], errorBag: 'group');
        });

        it('redirects unauthenticated guests to the login page', function () {
            post(route('groups.store'), [
                'name' => 'Unauthorized Group',
                'description' => 'Attempting guest post',
            ])->assertRedirect('/');
        });
    });

    describe('Update Group Action', function () {

        it('allows the group creator to update group details, members, projects, and profile', function () {
            $creator = User::factory()->create();
            $newMember = User::factory()->create();
            $newProject = Project::factory()->create();
            $newPaper = ResearchPaper::factory()->create();

            $group = Group::factory()->create([
                'creator_user_id' => $creator->id,
                'name' => 'Initial Group Name',
                'description' => 'Initial Group Description',
            ]);

            $payload = [
                'name' => 'Updated Group Name',
                'description' => 'Updated Group Description',
                'profile_bio' => 'Updated Profile Bio',
                'profile_details' => ['PHP 8.4', 'Pest PHP'],
                'picture_url' => 'https://example.com/new-cover.jpg',
                'member_ids' => [$newMember->id],
                'project_ids' => [$newProject->id],
                'paper_ids' => [$newPaper->id],
            ];

            actingAs($creator)
                ->from('/networking')
                ->patch(route('groups.update', $group), $payload)
                ->assertRedirect('/networking')
                ->assertSessionHas('status', 'Group updated.');

            assertDatabaseHas('groups', [
                'id' => $group->id,
                'name' => 'Updated Group Name',
                'description' => 'Updated Group Description',
            ]);

            assertDatabaseHas('group_profiles', [
                'group_id' => $group->id,
                'bio' => 'Updated Profile Bio',
                'picture_url' => 'https://example.com/new-cover.jpg',
            ]);

            expect($group->fresh()->projects->pluck('id'))->toContain($newProject->id);
            expect($group->fresh()->researchPapers->pluck('id'))->toContain($newPaper->id);
        });

        it('denies non-creators from updating the group and returns HTTP 403 Forbidden', function () {
            $creator = User::factory()->create();
            $otherUser = User::factory()->create();

            $group = Group::factory()->create([
                'creator_user_id' => $creator->id,
            ]);

            actingAs($otherUser)
                ->patch(route('groups.update', $group), [
                    'name' => 'Malicious Edit',
                    'description' => 'Attempting non-owner update',
                ])
                ->assertForbidden();
        });
    });

    describe('Destroy Group Action', function () {

        it('allows the group creator to delete the group', function () {
            $creator = User::factory()->create();
            $group = Group::factory()->create([
                'creator_user_id' => $creator->id,
            ]);

            actingAs($creator)
                ->from('/networking')
                ->delete(route('groups.destroy', $group))
                ->assertRedirect('/networking')
                ->assertSessionHas('status', 'Group deleted.');

            assertDatabaseMissing('groups', [
                'id' => $group->id,
            ]);
        });

        it('denies non-creators from deleting the group and returns HTTP 403 Forbidden', function () {
            $creator = User::factory()->create();
            $otherUser = User::factory()->create();

            $group = Group::factory()->create([
                'creator_user_id' => $creator->id,
            ]);

            actingAs($otherUser)
                ->delete(route('groups.destroy', $group))
                ->assertForbidden();

            assertDatabaseHas('groups', [
                'id' => $group->id,
            ]);
        });
    });
});
