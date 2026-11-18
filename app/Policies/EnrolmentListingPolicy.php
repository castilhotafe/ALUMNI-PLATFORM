<?php

namespace App\Policies;

use App\Models\EnrolmentListing;
use App\Models\User;

class EnrolmentListingPolicy
{
    public function create(User $user): bool
    {
        if (($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)) {
            return false;
        }

        return $user->hasPermissionTo('create enrolment listings');
    }

    public function update(User $user, EnrolmentListing $enrolmentListing): bool
    {
        if (($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)) {
            return false;
        }

        if (! $user->hasPermissionTo('create enrolment listings')) {
            return false;
        }

        return $user->hasRole('Admin') || $enrolmentListing->creator_user_id === $user->id;
    }

    public function delete(User $user, EnrolmentListing $enrolmentListing): bool
    {
        return $this->update($user, $enrolmentListing);
    }
}
