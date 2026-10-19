<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('provides resume_link on a freshly migrated profile table', function () {
    expect(Schema::hasColumn('user_profiles', 'resume_link'))->toBeTrue()
        ->and(Schema::hasColumn('user_profiles', 'resume_url'))->toBeFalse();

    $profile = Profile::create([
        'user_id' => User::factory()->create()->id,
        'resume_link' => 'https://example.com/resume.pdf',
    ]);

    expect($profile->fresh()->resume_link)->toBe('https://example.com/resume.pdf');

    $this->assertDatabaseHas('user_profiles', [
        'id' => $profile->id,
        'resume_link' => 'https://example.com/resume.pdf',
    ]);
});

it('preserves existing profile data when renaming and rolling back the resume column', function (?string $resumeUrl) {
    $migration = require database_path('migrations/2026_10_18_162500_rename_resume_url_to_resume_link_on_user_profiles_table.php');

    $migration->down();

    expect(Schema::hasColumn('user_profiles', 'resume_url'))->toBeTrue()
        ->and(Schema::hasColumn('user_profiles', 'resume_link'))->toBeFalse();

    $attributes = [
        'user_id' => User::factory()->create()->id,
        'bio' => 'Existing alumni biography',
        'details' => json_encode(['location' => 'Perth', 'skills' => ['PHP', 'Laravel']], JSON_THROW_ON_ERROR),
        'picture_url' => 'https://example.com/profile.png',
        'created_at' => '2026-10-01 09:30:00',
        'updated_at' => '2026-10-02 10:45:00',
    ];
    $profileId = DB::table('user_profiles')->insertGetId([
        ...$attributes,
        'resume_url' => $resumeUrl,
    ]);

    foreach (['up', 'down', 'up'] as $direction) {
        $migration->{$direction}();

        $resumeColumn = $direction === 'up' ? 'resume_link' : 'resume_url';
        $removedColumn = $direction === 'up' ? 'resume_url' : 'resume_link';

        expect(Schema::hasColumn('user_profiles', $resumeColumn))->toBeTrue()
            ->and(Schema::hasColumn('user_profiles', $removedColumn))->toBeFalse();

        $this->assertDatabaseCount('user_profiles', 1);
        $this->assertDatabaseHas('user_profiles', [
            'id' => $profileId,
            ...$attributes,
            $resumeColumn => $resumeUrl,
        ]);
    }
})->with([
    'existing resume URL' => ['https://example.com/existing-resume.pdf?version=2'],
    'null resume URL' => [null],
]);
