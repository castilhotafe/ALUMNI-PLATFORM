<?php

namespace App\Http\Controllers;

use App\Events\ContributionTriggered;
use App\Http\Requests\StoreEnrolmentListingRequest;
use App\Http\Requests\UpdateEnrolmentListingRequest;
use App\Models\EnrolmentListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class EnrolmentListingController extends Controller
{
    private function makeListingProfileData(array $validated, string $prefix): array
    {
        return [
            'bio' => $validated["{$prefix}_profile_bio"] ?? null,
            'details' => collect($validated["{$prefix}_profile_details"] ?? [])
                ->filter(fn ($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated["{$prefix}_picture_url"] ?? null,
        ];
    }

    public function store(StoreEnrolmentListingRequest $request): RedirectResponse
    {
        Gate::authorize('create', EnrolmentListing::class);

        $enrolmentListing = $request->user()->enrolmentListings()->create(Arr::only($request->validated(), [
            'course_title',
            'tafe_provider',
            'description',
            'enrol_link',
        ]));

        $profileData = $this->makeListingProfileData($request->validated(), 'enrolment');
        if (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $enrolmentListing->profile()->create($profileData);
        }

        return back()->with('status_enrolment_listing', 'Enrolment listing created.');
    }

    public function update(UpdateEnrolmentListingRequest $request, EnrolmentListing $enrolmentListing): RedirectResponse
    {
        Gate::authorize('update', $enrolmentListing);

        $enrolmentListing->update(Arr::only($request->validated(), [
            'course_title',
            'tafe_provider',
            'description',
            'enrol_link',
        ]));

        $profileData = $this->makeListingProfileData($request->validated(), 'enrolment');
        if ($enrolmentListing->profile) {
            $enrolmentListing->profile->update($profileData);
        } elseif (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $enrolmentListing->profile()->create($profileData);
        }

        return back()->with('status_enrolment_listing_manage', 'Enrolment listing updated.');
    }

    public function destroy(Request $request, EnrolmentListing $enrolmentListing): RedirectResponse
    {
        Gate::authorize('delete', $enrolmentListing);

        $enrolmentListing->delete();

        return back()->with('status_enrolment_listing_manage', 'Enrolment listing deleted.');
    }

    public function trackEnrolmentLink(Request $request, EnrolmentListing $enrolmentListing): RedirectResponse
    {
        ContributionTriggered::dispatch($request->user(), 'enrolment_link_clicked', $enrolmentListing);
        $destination = $enrolmentListing->enrol_link ?: route('discover');

        return redirect()->away($destination);
    }
}
