<?php

use App\Models\EnrolmentListing;
use App\Models\EnrolmentListingProfile;
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
    // Safely bypass CSRF middleware during test execution
    $this->withoutMiddleware(PreventRequestForgery::class);

    // Ensure permissions and roles exist
    Permission::findOrCreate('create enrolment listings');
    Role::findOrCreate('Admin');
});

describe('Enrolment Listing Store Action & Validation', function () {

    it('allows an authorized user to create an enrolment listing with optional profile metadata', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create enrolment listings');

        $payload = [
            'course_title' => 'Diploma of Information Technology',
            'tafe_provider' => 'North Metropolitan TAFE',
            'description' => 'Comprehensive course covering full-stack software development.',
            'enrol_link' => 'https://www.northtafe.wa.edu.au/courses/diploma-it',
            'enrolment_profile_bio' => 'Great program for prospective software engineers.',
            'enrolment_profile_details' => ['Diploma', 'Joondalup Campus', 'Full-time'],
            'enrolment_picture_url' => 'https://example.com/banner.png',
        ];

        actingAs($user)
            ->from('/discover')
            ->post(route('enrolment-listings.store'), $payload)
            ->assertRedirect('/discover')
            ->assertSessionHas('status_enrolment_listing', 'Enrolment listing created.');

        assertDatabaseHas('enrolment_listings', [
            'creator_user_id' => $user->id,
            'course_title' => 'Diploma of Information Technology',
            'tafe_provider' => 'North Metropolitan TAFE',
            'enrol_link' => 'https://www.northtafe.wa.edu.au/courses/diploma-it',
        ]);

        $enrolmentListing = EnrolmentListing::where('course_title', 'Diploma of Information Technology')->first();

        assertDatabaseHas('enrolment_listing_profiles', [
            'enrolment_listing_id' => $enrolmentListing->id,
            'bio' => 'Great program for prospective software engineers.',
            'picture_url' => 'https://example.com/banner.png',
        ]);

        expect($enrolmentListing->profile->details)->toBe(['Diploma', 'Joondalup Campus', 'Full-time']);
    });

    it('creates an enrolment listing without profile record if optional profile fields are omitted', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create enrolment listings');

        actingAs($user)
            ->post(route('enrolment-listings.store'), [
                'course_title' => 'Certificate IV in Cyber Security',
                'tafe_provider' => 'North Metropolitan TAFE',
                'description' => 'Entry level cybersecurity qualification.',
            ])
            ->assertSessionHas('status_enrolment_listing', 'Enrolment listing created.');

        $enrolmentListing = EnrolmentListing::where('course_title', 'Certificate IV in Cyber Security')->first();
        expect($enrolmentListing->profile)->toBeNull();
    });

    it('redirects with enrolment_listing error bag when required fields are missing', function () {
        $user = User::factory()->create();
        $user->givePermissionTo('create enrolment listings');

        actingAs($user)
            ->from('/discover')
            ->post(route('enrolment-listings.store'), [])
            ->assertRedirect('/discover')
            ->assertSessionHasErrors(['course_title', 'tafe_provider', 'description'], errorBag: 'enrolment_listing');
    });

    it('denies unverified partners or lecturers from creating enrolment listings', function () {
        $unverifiedLecturer = User::factory()->create([
            'isLecturer' => true,
            'isVerifiedLecturer' => false,
        ]);
        $unverifiedLecturer->givePermissionTo('create enrolment listings');

        actingAs($unverifiedLecturer)
            ->post(route('enrolment-listings.store'), [
                'course_title' => 'Unauthorized Course',
                'tafe_provider' => 'Unverified TAFE',
                'description' => 'Testing policy guardrail.',
            ])
            ->assertForbidden();
    });

    it('denies users missing the create enrolment listings permission', function () {
        $user = User::factory()->create(); // No permission granted

        actingAs($user)
            ->post(route('enrolment-listings.store'), [
                'course_title' => 'Unauthorized Course',
                'tafe_provider' => 'TAFE Provider',
                'description' => 'Testing policy check.',
            ])
            ->assertForbidden();
    });
});

describe('Enrolment Listing Update Action', function () {

    it('allows the creator to update enrolment listing details and profile', function () {
        $creator = User::factory()->create();
        $creator->givePermissionTo('create enrolment listings');

        $enrolmentListing = EnrolmentListing::factory()->create([
            'creator_user_id' => $creator->id,
            'course_title' => 'Original Course Title',
        ]);

        $payload = [
            'course_title' => 'Updated Course Title',
            'tafe_provider' => 'Updated TAFE Provider',
            'description' => 'Updated course description.',
            'enrol_link' => 'https://careers.example.com/updated-enrol',
            'enrolment_profile_bio' => 'Updated profile bio.',
            'enrolment_profile_details' => ['Part-time', 'Perth Campus'],
        ];

        actingAs($creator)
            ->from('/discover')
            ->patch(route('enrolment-listings.update', $enrolmentListing), $payload)
            ->assertRedirect('/discover')
            ->assertSessionHas('status_enrolment_listing_manage', 'Enrolment listing updated.');

        assertDatabaseHas('enrolment_listings', [
            'id' => $enrolmentListing->id,
            'course_title' => 'Updated Course Title',
            'tafe_provider' => 'Updated TAFE Provider',
        ]);

        assertDatabaseHas('enrolment_listing_profiles', [
            'enrolment_listing_id' => $enrolmentListing->id,
            'bio' => 'Updated profile bio.',
        ]);
    });

    it('allows an Admin user to update an enrolment listing created by someone else', function () {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo('create enrolment listings');

        $enrolmentListing = EnrolmentListing::factory()->create();

        actingAs($admin)
            ->patch(route('enrolment-listings.update', $enrolmentListing), [
                'course_title' => 'Admin Moderated Course Title',
                'tafe_provider' => 'TAFE Provider',
                'description' => 'Moderated course description.',
            ])
            ->assertSessionHas('status_enrolment_listing_manage', 'Enrolment listing updated.');

        assertDatabaseHas('enrolment_listings', [
            'id' => $enrolmentListing->id,
            'course_title' => 'Admin Moderated Course Title',
        ]);
    });

    it('denies non-creator users from updating another users enrolment listing', function () {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->givePermissionTo('create enrolment listings');

        $enrolmentListing = EnrolmentListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($otherUser)
            ->patch(route('enrolment-listings.update', $enrolmentListing), [
                'course_title' => 'Unauthorized Modification',
                'tafe_provider' => 'Hacker TAFE',
                'description' => 'Should be blocked.',
            ])
            ->assertForbidden();
    });
});

describe('Enrolment Listing Destroy Action', function () {

    it('allows the creator to delete their enrolment listing', function () {
        $creator = User::factory()->create();
        $creator->givePermissionTo('create enrolment listings');

        $enrolmentListing = EnrolmentListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($creator)
            ->from('/discover')
            ->delete(route('enrolment-listings.destroy', $enrolmentListing))
            ->assertRedirect('/discover')
            ->assertSessionHas('status_enrolment_listing_manage', 'Enrolment listing deleted.');

        assertDatabaseMissing('enrolment_listings', ['id' => $enrolmentListing->id]);
    });

    it('denies non-creators from deleting an enrolment listing', function () {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();

        $enrolmentListing = EnrolmentListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        actingAs($otherUser)
            ->delete(route('enrolment-listings.destroy', $enrolmentListing))
            ->assertForbidden();

        assertDatabaseHas('enrolment_listings', ['id' => $enrolmentListing->id]);
    });
});

describe('Enrolment Link Tracking', function () {

    it('redirects to the external enrolment link when available', function () {
        $user = User::factory()->create();
        $enrolmentListing = EnrolmentListing::factory()->create([
            'enrol_link' => 'https://external-enrolment.example.com/apply',
        ]);

        actingAs($user)
            ->get(route('enrolment-listings.enrol', $enrolmentListing))
            ->assertRedirect('https://external-enrolment.example.com/apply');
    });

    it('redirects to discover route as fallback if enrolment link is empty', function () {
        $user = User::factory()->create();
        $enrolmentListing = EnrolmentListing::factory()->create([
            'enrol_link' => null,
        ]);

        actingAs($user)
            ->get(route('enrolment-listings.enrol', $enrolmentListing))
            ->assertRedirect(route('discover'));
    });
});

describe('EnrolmentListing & EnrolmentListingProfile Model Relationships and Casts', function () {

    it('belongs to a creator user', function () {
        $creator = User::factory()->create();
        $enrolmentListing = EnrolmentListing::factory()->create([
            'creator_user_id' => $creator->id,
        ]);

        expect($enrolmentListing->creator)->toBeInstanceOf(User::class)
            ->and($enrolmentListing->creator->id)->toBe($creator->id);
    });

    it('has a profile relationship', function () {
        $enrolmentListing = EnrolmentListing::factory()->create();
        $profile = EnrolmentListingProfile::factory()->create([
            'enrolment_listing_id' => $enrolmentListing->id,
        ]);

        expect($enrolmentListing->profile)->toBeInstanceOf(EnrolmentListingProfile::class)
            ->and($enrolmentListing->profile->id)->toBe($profile->id);
    });

    it('allows EnrolmentListingProfile to belong back to an EnrolmentListing instance', function () {
        $enrolmentListing = EnrolmentListing::factory()->create();
        $profile = EnrolmentListingProfile::factory()->create([
            'enrolment_listing_id' => $enrolmentListing->id,
        ]);

        expect($profile->enrolmentListing)->toBeInstanceOf(EnrolmentListing::class)
            ->and($profile->enrolmentListing->id)->toBe($enrolmentListing->id);
    });

    it('casts details attribute to array on EnrolmentListingProfile', function () {
        $profile = EnrolmentListingProfile::factory()->create([
            'details' => ['Diploma', 'Joondalup', 'Full-time'],
        ]);

        expect($profile->details)->toBeArray()
            ->toBe(['Diploma', 'Joondalup', 'Full-time']);
    });
});
