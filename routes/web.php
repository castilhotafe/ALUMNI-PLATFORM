<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContributionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\EnrolmentListingController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest Routes (Only for logged-out users)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Auth Routes (Only for logged-in users)
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Authenticated and Verified Routes (Only for logged-in users with verified email)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [PlatformController::class, 'dashboard'])->name('dashboard');
    Route::get('/projects', [PlatformController::class, 'projects'])->name('projects');
    Route::get('/papers', [PlatformController::class, 'papers'])->name('papers');
    Route::get('/me', [PlatformController::class, 'me'])->name('me');
    Route::get('/directory', [PlatformController::class, 'directory'])->name('directory');
    Route::get('/networking-hub', [PlatformController::class, 'networking'])->name('networking');
    Route::get('/discover-hub', [PlatformController::class, 'discover'])->name('discover');

    // Project Routes
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Research Paper Routes
    Route::post('/papers', [PaperController::class, 'store'])->name('papers.store');
    Route::patch('/papers/{paper}', [PaperController::class, 'update'])->name('papers.update');
    Route::delete('/papers/{paper}', [PaperController::class, 'destroy'])->name('papers.destroy');

    // Profile Routes (User Email, Password, and Profile Management)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Post Routes
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Resume Routes (Profile)
    Route::patch('/me/profile', [MeController::class, 'update'])->name('me.profile.update');

    // Group Routes
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::patch('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

    // Job Listing Routes
    Route::post('/job-listings', [JobListingController::class, 'store'])
        ->name('job-listings.store')
        ->middleware('permission:create job listings');
    Route::patch('/job-listings/{job_listing}', [JobListingController::class, 'update'])
        ->name('job-listings.update')
        ->middleware('permission:create job listings');
    Route::delete('/job-listings/{job_listing}', [JobListingController::class, 'destroy'])
        ->name('job-listings.destroy')
        ->middleware('permission:create job listings');
    Route::get('/job-listings/{job_listing}/apply', [JobListingController::class, 'trackJobApplication'])->name('job-listings.apply');

    // Enrolment Listing Routes
    Route::post('/enrolment-listings', [EnrolmentListingController::class, 'store'])
        ->name('enrolment-listings.store')
        ->middleware('permission:create enrolment listings');
    Route::patch('/enrolment-listings/{enrolment_listing}', [EnrolmentListingController::class, 'update'])
        ->name('enrolment-listings.update')
        ->middleware('permission:create enrolment listings');
    Route::delete('/enrolment-listings/{enrolment_listing}', [EnrolmentListingController::class, 'destroy'])
        ->name('enrolment-listings.destroy')
        ->middleware('permission:create enrolment listings');
    Route::get('/enrolment-listings/{enrolment_listing}/enrol', [EnrolmentListingController::class, 'trackEnrolmentLink'])->name('enrolment-listings.enrol');

    // Admin Routes
    Route::get('/admin', [AdminController::class, 'dashboard'])
        ->middleware('role:Admin')
        ->name('admin.dashboard');
    Route::get('/admin/contributions', [ContributionController::class, 'index'])
        ->middleware('role:Admin')
        ->name('admin.contributions.index');
    Route::post('/admin/contributions', [ContributionController::class, 'store'])
        ->middleware('role:Admin')
        ->name('admin.contributions.store');
    Route::patch('/admin/users/{user}/verify', [AdminController::class, 'updateVerification'])
        ->middleware('role:Admin')
        ->name('admin.users.verify');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])
        ->middleware('role:Admin')
        ->name('admin.users.store');
    Route::patch('/admin/users/{user}', [AdminController::class, 'updateUser'])
        ->middleware('role:Admin')
        ->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])
        ->middleware('role:Admin')
        ->name('admin.users.destroy');
});
