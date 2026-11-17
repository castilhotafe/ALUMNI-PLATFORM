<?php

namespace App\Http\Controllers;

use App\Events\ContributionTriggered;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class JobListingController extends Controller
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

    public function store(StoreJobListingRequest $request): RedirectResponse
    {
        Gate::authorize('create', JobListing::class);

        $jobListing = $request->user()->jobListings()->create(Arr::only($request->validated(), [
            'title',
            'company',
            'description',
            'apply_link',
        ]));

        $profileData = $this->makeListingProfileData($request->validated(), 'job');
        if (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $jobListing->profile()->create($profileData);
        }

        return back()->with('status_job_listing', 'Job listing created.');
    }

    public function update(UpdateJobListingRequest $request, JobListing $jobListing): RedirectResponse
    {
        Gate::authorize('update', $jobListing);

        $jobListing->update(Arr::only($request->validated(), [
            'title',
            'company',
            'description',
            'apply_link',
        ]));

        $profileData = $this->makeListingProfileData($request->validated(), 'job');
        if ($jobListing->profile) {
            $jobListing->profile->update($profileData);
        } elseif (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $jobListing->profile()->create($profileData);
        }

        return back()->with('status_job_listing_manage', 'Job listing updated.');
    }

    public function destroy(Request $request, JobListing $jobListing): RedirectResponse
    {
        Gate::authorize('delete', $jobListing);

        $jobListing->delete();

        return back()->with('status_job_listing_manage', 'Job listing deleted.');
    }

    public function trackJobApplication(Request $request, JobListing $jobListing): RedirectResponse
    {
        ContributionTriggered::dispatch($request->user(), 'job_application_clicked', $jobListing);
        $destination = $jobListing->apply_link ?: route('discover');

        return redirect()->away($destination);
    }
}
