<?php

namespace App\Providers;

use App\Models\EnrolmentListing;
use App\Models\JobListing;
use App\Policies\EnrolmentListingPolicy;
use App\Policies\JobListingPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JobListing::class, JobListingPolicy::class);
        Gate::policy(EnrolmentListing::class, EnrolmentListingPolicy::class);
    }
}
