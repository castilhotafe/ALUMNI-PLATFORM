<?php

use App\Models\JobListing;
use App\Models\JobListingProfile;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure default permissions and roles exist
    Permission::findOrCreate('create job listings');
    Role::findOrCreate('Admin');
    $this->withoutMiddleware(PreventRequestForgery::class);
});

describe('Job Listing Store Action & Validation', function () {

    it('allows an authorized user to create a job listing with optional profile metadata', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create job listings');

        $payload = [
            'title' => 'Senior Laravel Developer',
            'company' => 'Tech Solutions Ltd',
            'description' => 'Looking for an experienced backend developer.',
            'apply_link' => 'https://careers.example.com/apply/123',
            'job_profile_bio' => 'Great company culture and remote work options.',
            'job_profile_details' => ['Full-time', 'Perth WA', 'Hybrid'],
            'job_picture_url' => 'https://example.com/banner.png',
        ];

        actingAs($user)
            ->from('/discover')
            ->post(route('job-listings.store'), $payload)
            ->assertRedirect('/discover')
            ->assertSessionHas('status_job_listing', 'Job listing created.');

        assertDatabaseHas('job_listings', [
            'creator_user_id' => $user->id,
            'title' => 'Senior Laravel Developer',
            'company' => 'Tech Solutions Ltd',
            'apply_link' => 'https://careers.example.com/apply/123',
        ]);

        $jobListing = JobListing::where('title', 'Senior Laravel Developer')->first();

        assertDatabaseHas('job_listing_profiles', [
            'job_listing_id' => $jobListing->id,
            'bio' => 'Great company culture and remote work options.',
            'picture_url' => 'https://example.com/banner.png',
        ]);

        expect($jobListing->profile->details)->toBe(['Full-time', 'Perth WA', 'Hybrid']);
    });

    it('creates a job listing without profile record if optional profile fields are omitted', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create job listings');

        actingAs($user)
            ->post(route('job-listings.store'), [
                'title' => 'Junior Web Developer',
                'company' => 'StartUp Inc',
                'description' => 'Entry level web role.',
            ])
            ->assertSessionHas('status_job_listing', 'Job listing created.');

        $jobListing = JobListing::where('title', 'Junior Web Developer')->first();
        expect($jobListing->profile)->toBeNull();
    });

    it('redirects with job_listing error bag when required fields are missing', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create job listings');

        actingAs($user)
            ->from('/discover')
            ->post(route('job-listings.store'), [])
            ->assertRedirect('/discover')
            ->assertSessionHasErrors(['title', 'company', 'description'], errorBag: 'job_listing');
    });

    it('denies unverified partners or lecturers from creating job listings', function () {
        $unverifiedPartner = User::factory()->create([
            'isPartner' => true,
            'isVerifiedPartner' => false,
        ]);
        $unverifiedPartner->givePermissionTo('create job listings');

        actingAs($unverifiedPartner)
            ->post(route('job-listings.store'), [
                'title' => 'Unauthorized Job',
                'company' => 'Partner Co',
                'description' => 'Testing policy guardrail.',
            ])
            ->assertForbidden();
    });

    it('denies users missing the create job listings permission', function () {
        $user = User::factory()->create(); // No permission granted

        actingAs($user)
            ->post(route('job-listings.store'), [
                'title' => 'Unauthorized Job',
                'company' => 'Company',
                'description' => 'Testing policy check.',
            ])
            ->assertForbidden();
    });
});

describe('Job Listing Update Action', function () {

    it('allows the creator to update job listing details and profile', function () {
        $creator = User::factory()->create();
        $creator->givePermissionTo('create job listings');

        $jobListing = JobListing::factory()->create([
            'creator_user_id' => $creator->id,
            'title' => 'Original Title',
        ]);

        $payload = [
            'title' => 'Updated Job Title',
            'company' => 'Updated Company',
            'description' => 'Updated description content.',
            'apply_link' => 'https://careers.example.com/updated',
            'job_profile_bio' => 'Updated profile bio.',
            'job_profile_details' => ['Remote', 'Senior'],
        ];

        actingAs($creator)
            ->from('/discover')
            ->patch(route('job-listings.update', $jobListing), $payload)
            ->assertRedirect('/discover')
            ->assertSessionHas('status_job_listing_manage', 'Job listing updated.');

        assertDatabaseHas('job_listings', [
            'id' => $jobListing->id,
            'title' => 'Updated Job Title',
            'company' => 'Updated Company',
        ]);

        assertDatabaseHas('job_listing_profiles', [
            'job_listing_id' => $jobListing->id,
            'bio' => 'Updated profile bio.',
        ]);
    });

    it('allows an Admin user to update a job listing created by someone else', function () {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo('create job listings');

        $jobListing = JobListing::factory()->create();

        actingAs($admin)
            ->patch(route('job-listings.update', $jobListing), [
                'title' => 'Admin Moderated Title',
                'company' => 'Company',
                'description' => 'Moderated description.',
            ])
            ->assertSessionHas('status_job_listing_manage', 'Job listing updated.');

        assertDatabaseHas('job_listings', [
            'id' => $jobListing->id,
            'title' => 'Admin Moderated Title',
        ]);
    });

    it('denies non-creator users from updating another users job listing', function () {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->givePermissionTo('create job listings');

        $jobListing = JobListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($otherUser)
            ->patch(route('job-listings.update', $jobListing), [
                'title' => 'Unauthorized Modification',
                'company' => 'Hacker Inc',
                'description' => 'Should be blocked.',
            ])
            ->assertForbidden();
    });
});

describe('Job Listing Destroy Action', function () {

    it('allows the creator to delete their job listing', function () {
        $creator = User::factory()->create();
        $creator->givePermissionTo('create job listings');

        $jobListing = JobListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($creator)
            ->from('/discover')
            ->delete(route('job-listings.destroy', $jobListing))
            ->assertRedirect('/discover')
            ->assertSessionHas('status_job_listing_manage', 'Job listing deleted.');

        assertDatabaseMissing('job_listings', ['id' => $jobListing->id]);
    });

    it('denies non-creators from deleting a job listing', function () {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $jobListing = JobListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($otherUser)
            ->delete(route('job-listings.destroy', $jobListing))
            ->assertForbidden();

        assertDatabaseHas('job_listings', ['id' => $jobListing->id]);
    });
});

describe('Job Listing Application Redirection Tracking', function () {

    it('redirects to the job external apply link when available', function () {
        $user = User::factory()->create();
        $jobListing = JobListing::factory()->create([
            'apply_link' => 'https://external-job-listings.example.com/apply',
        ]);

        actingAs($user)
            ->get(route('job-listings.apply', $jobListing))
            ->assertRedirect('https://external-job-listings.example.com/apply');
    });

    it('redirects to discover route as fallback if apply link is empty', function () {
        $user = User::factory()->create();
        $jobListing = JobListing::factory()->create([
            'apply_link' => null,
        ]);

        actingAs($user)
            ->get(route('job-listings.apply', $jobListing))
            ->assertRedirect(route('discover'));
    });
});

describe('JobListing & JobListingProfile Model Relationships and Casts', function () {

    it('belongs to a creator user', function () {
        $creator = User::factory()->create();
        $jobListing = JobListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        expect($jobListing->creator)->toBeInstanceOf(User::class)
            ->and($jobListing->creator->id)->toBe($creator->id);
    });

    it('has a profile relationship', function () {
        $jobListing = JobListing::factory()->create();
        $profile = JobListingProfile::factory()->create([
            'job_listing_id' => $jobListing->id,
        ]);

        expect($jobListing->profile)->toBeInstanceOf(JobListingProfile::class)
            ->and($jobListing->profile->id)->toBe($profile->id);
    });

    it('allows JobListingProfile to belong back to a JobListing instance', function () {
        $jobListing = JobListing::factory()->create();
        $profile = JobListingProfile::factory()->create([
            'job_listing_id' => $jobListing->id,
        ]);

        expect($profile->jobListing)->toBeInstanceOf(JobListing::class)
            ->and($profile->jobListing->id)->toBe($jobListing->id);
    });

    it('casts details attribute to array on JobListingProfile', function () {
        $profile = JobListingProfile::factory()->create([
            'details' => ['Full-time', 'Perth WA', 'Hybrid'],
        ]);

        expect($profile->details)->toBeArray()
            ->toBe(['Full-time', 'Perth WA', 'Hybrid']);
    });
});
