<?php

use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('User Model Attributes & Casting', function () {

    it('casts boolean flags, student ID, and datetime attributes correctly', function () {
        $user = User::factory()->create([
            'student_id' => '12345678',
            'isCurrent' => 1,
            'isAlumni' => 0,
            'isLecturer' => '1',
            'isPartner' => false,
            'isVerifiedCurrent' => 1,
            'isVerifiedAlumni' => 0,
            'isVerifiedLecturer' => 0,
            'isVerifiedPartner' => 0,
            'email_verified_at' => '2026-01-01 12:00:00',
        ]);

        expect($user->student_id)->toBeInt()->toBe(12345678);
        expect($user->isCurrent)->toBeBool()->toBeTrue();
        expect($user->isAlumni)->toBeBool()->toBeFalse();
        expect($user->isLecturer)->toBeBool()->toBeTrue();
        expect($user->isPartner)->toBeBool()->toBeFalse();
        expect($user->isVerifiedCurrent)->toBeBool()->toBeTrue();
        expect($user->isVerifiedAlumni)->toBeBool()->toBeFalse();
        expect($user->email_verified_at)->toBeInstanceOf(Carbon::class);
    });
});

describe('User Model Relationships', function () {

    it('has a profile relationship (HasOne)', function () {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        expect($user->profile)->toBeInstanceOf(Profile::class)
            ->and($user->profile->id)->toBe($profile->id);
    });

    it('has posts relationship (HasMany)', function () {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['user_id' => $user->id]);

        expect($user->posts)->toHaveCount(3)
            ->and($user->posts->first())->toBeInstanceOf(Post::class);
    });

    it('belongs to many projects with contribution_role pivot data', function () {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $user->projects()->attach($project->id, ['contribution_role' => 'Lead Developer']);

        expect($user->projects)->toHaveCount(1)
            ->and($user->projects->first()->pivot->contribution_role)->toBe('Lead Developer');
    });

    it('belongs to many research papers with author_order pivot data', function () {
        $user = User::factory()->create();
        $paper = ResearchPaper::factory()->create();

        $user->researchPapers()->attach($paper->id, ['author_order' => 1]);

        expect($user->researchPapers)->toHaveCount(1)
            ->and($user->researchPapers->first()->pivot->author_order)->toBe(1);
    });
});

describe('User Model Role Label Logic (selfDeclaredRoleLabel)', function () {

    it('returns "Current Student" when isCurrent flag is true', function () {
        $user = User::factory()->make(['isCurrent' => true]);
        expect($user->selfDeclaredRoleLabel())->toBe('Current Student');
    });

    it('returns "Alumni" when isAlumni flag is true', function () {
        $user = User::factory()->make(['isAlumni' => true]);
        expect($user->selfDeclaredRoleLabel())->toBe('Alumni');
    });

    it('returns "Lecturer" when isLecturer flag is true', function () {
        $user = User::factory()->make(['isLecturer' => true]);
        expect($user->selfDeclaredRoleLabel())->toBe('Lecturer');
    });

    it('returns "Partner" when isPartner flag is true', function () {
        $user = User::factory()->make(['isPartner' => true]);
        expect($user->selfDeclaredRoleLabel())->toBe('Partner');
    });

    it('returns "General User" when Spatie role "General User" is assigned and no boolean flag is set', function () {
        Role::findOrCreate('General User');

        $user = User::factory()->create([
            'isCurrent' => false,
            'isAlumni' => false,
            'isLecturer' => false,
            'isPartner' => false,
        ]);

        $user->assignRole('General User');

        expect($user->selfDeclaredRoleLabel())->toBe('General User');
    });

    it('returns null when no role flags or General User Spatie role exist', function () {
        $user = User::factory()->make([
            'isCurrent' => false,
            'isAlumni' => false,
            'isLecturer' => false,
            'isPartner' => false,
        ]);

        expect($user->selfDeclaredRoleLabel())->toBeNull();
    });
});
