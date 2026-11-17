<?php

namespace App\Policies;

use App\Models\JobListing;
use App\Models\User;

class JobListingPolicy
{
    public function create(User $user): bool
    {
        if (($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)) {
            return false;
        }

        return $user->hasPermissionTo('create job listings');
    }

    public function update(User $user, JobListing $jobListing): bool
    {
        if (($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)) {
            return false;
        }

        if (! $user->hasPermissionTo('create job listings')) {
            return false;
        }

        return $user->hasRole('Admin') || $jobListing->creator_user_id === $user->id;
    }

    public function delete(User $user, JobListing $jobListing): bool
    {
        return $this->update($user, $jobListing);
    }
}
