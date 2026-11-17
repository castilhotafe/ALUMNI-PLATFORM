<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\EnrolmentListing;
use App\Models\Group;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $users = User::with('roles')->orderBy('name')->get();

        $roleMap = [
            'Current Student' => 'current',
            'Lecturer' => 'lecturer',
            'Partner' => 'partner',
            'Alumni' => 'alumni',
            'General User' => 'general',
            'Admin' => 'admin',
        ];

        $roleLabels = [
            'current' => 'Current Student',
            'lecturer' => 'Lecturer',
            'partner' => 'Partner',
            'alumni' => 'Alumni',
            'general' => 'General User',
            'admin' => 'Admin',
        ];

        // Prepare the payload for Alpine.js
        $usersPayload = $users->map(function ($user) use ($roleMap, $roleLabels) {
            $roleName = $user->hasRole('Admin') ? 'Admin' : $user->getRoleNames()->first() ?? 'General User';
            $roleKey = $roleMap[$roleName] ?? 'general';

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roleKey' => $roleKey,
                'roleLabel' => $roleLabels[$roleKey] ?? $roleName,
                // Partners and lecturers must be explicitly verified by an admin; show pending until verified
                'status' => (($roleKey === 'partner' && ! $user->isVerifiedPartner) || ($roleKey === 'lecturer' && ! $user->isVerifiedLecturer))
                    ? 'pending'
                    : ($user->email_verified_at ? 'active' : 'pending'),
                'studentId' => $user->student_id,
                'emailVerified' => (bool) $user->email_verified_at,
                'passwordConfirmed' => (bool) $user->password_confirmed_at,
                'isAdmin' => $roleKey === 'admin',
                'selfDeclaredRole' => $user->selfDeclaredRoleLabel(),
                'isVerifiedCurrent' => (bool) $user->isVerifiedCurrent,
                'isVerifiedAlumni' => (bool) $user->isVerifiedAlumni,
                'isVerifiedLecturer' => (bool) $user->isVerifiedLecturer,
                'isVerifiedPartner' => (bool) $user->isVerifiedPartner,
            ];
        });

        // Capture old input for form validation errors
        $oldForm = [
            'mode' => old('form_mode'),
            'id' => old('user_id'),
            'name' => old('name'),
            'email' => old('email'),
            'roleSelection' => old('role_selection'),
            'status' => old('status'),
            'studentId' => old('student_id'),
            'emailVerified' => old('emailVerified'),
            'isVerifiedCurrent' => old('isVerifiedCurrent'),
            'isVerifiedAlumni' => old('isVerifiedAlumni'),
            'isVerifiedLecturer' => old('isVerifiedLecturer'),
            'isVerifiedPartner' => old('isVerifiedPartner'),
        ];

        $totalUsers = $users->count();
        $roleCounts = $users
            ->map(fn ($user) => $this->effectiveRoleKey($user, $roleMap))
            ->countBy();

        $adminCount = (int) ($roleCounts->get('admin') ?? 0);
        $studentCount = (int) ($roleCounts->get('current') ?? 0);
        $alumniCount = (int) ($roleCounts->get('alumni') ?? 0);
        $lecturerCount = (int) ($roleCounts->get('lecturer') ?? 0);
        $partnerCount = (int) ($roleCounts->get('partner') ?? 0);
        $generalCount = (int) ($roleCounts->get('general') ?? 0);

        $activeUsers = $users->filter(
            fn ($user) => $user->email_verified_at !== null && (! $user->isPartner || $user->isVerifiedPartner) && (! $user->isLecturer || $user->isVerifiedLecturer)
        )->count();

        $pendingUsers = $users->filter(
            fn ($user) => $user->email_verified_at === null || ($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)
        )->count();

        $emailVerifiedUsers = $users->filter(fn ($user) => $user->email_verified_at !== null)->count();
        $verifiedPartnerCount = $users
            ->filter(fn ($user) => $this->effectiveRoleKey($user, $roleMap) === 'partner' && $user->isVerifiedPartner)
            ->count();
        $verifiedLecturerCount = $users
            ->filter(fn ($user) => $this->effectiveRoleKey($user, $roleMap) === 'lecturer' && $user->isVerifiedLecturer)
            ->count();

        $totalProjects = Project::count();
        $totalPosts = Post::count();
        $totalPapers = ResearchPaper::count();
        $totalGroups = Group::count();
        $totalJobListings = JobListing::count();
        $totalEnrolmentListings = EnrolmentListing::count();

        $totalListings = $totalJobListings + $totalEnrolmentListings;
        $totalContentItems = $totalProjects + $totalPosts + $totalPapers;

        $statsSections = [
            [
                'title' => 'User overview',
                'items' => [
                    ['label' => 'Total users', 'value' => $totalUsers, 'note' => 'Accounts in the directory'],
                    ['label' => 'Active users', 'value' => $activeUsers, 'note' => 'Email verified and active'],
                    ['label' => 'Pending users', 'value' => $pendingUsers, 'note' => 'Awaiting verification'],
                    ['label' => 'Admins', 'value' => $adminCount, 'note' => 'Role changes locked'],
                    ['label' => 'Email verified', 'value' => $emailVerifiedUsers, 'note' => 'Users with verified email'],
                ],
            ],
            [
                'title' => 'User roles',
                'items' => [
                    ['label' => 'Students', 'value' => $studentCount, 'note' => 'Current students'],
                    ['label' => 'Alumni', 'value' => $alumniCount, 'note' => 'Graduate network'],
                    ['label' => 'Lecturers', 'value' => $lecturerCount, 'note' => 'Teaching staff accounts'],
                    ['label' => 'Partners', 'value' => $partnerCount, 'note' => 'Industry partner accounts'],
                    ['label' => 'General users', 'value' => $generalCount, 'note' => 'Unassigned/general accounts'],
                ],
            ],
            [
                'title' => 'Platform content',
                'items' => [
                    ['label' => 'Total projects', 'value' => $totalProjects, 'note' => 'Project records created'],
                    ['label' => 'Total posts', 'value' => $totalPosts, 'note' => 'Community feed posts'],
                    ['label' => 'Research papers', 'value' => $totalPapers, 'note' => 'Published paper records'],
                    ['label' => 'Groups', 'value' => $totalGroups, 'note' => 'Networking groups'],
                    ['label' => 'Total listings', 'value' => $totalListings, 'note' => 'Job + enrolment listings'],
                    ['label' => 'Job listings', 'value' => $totalJobListings, 'note' => 'Open and archived job posts'],
                    ['label' => 'Enrolment listings', 'value' => $totalEnrolmentListings, 'note' => 'Course and enrolment opportunities'],
                ],
            ],
            [
                'title' => 'Calculated stats',
                'items' => [
                    ['label' => 'Active rate', 'value' => $this->formatPercentage($activeUsers, $totalUsers), 'note' => 'Active users / total users'],
                    ['label' => 'Email verified rate', 'value' => $this->formatPercentage($emailVerifiedUsers, $totalUsers), 'note' => 'Email verified users / total users'],
                    ['label' => 'Partner verification rate', 'value' => $this->formatPercentage($verifiedPartnerCount, $partnerCount), 'note' => 'Verified partners / partner users'],
                    ['label' => 'Lecturer verification rate', 'value' => $this->formatPercentage($verifiedLecturerCount, $lecturerCount), 'note' => 'Verified lecturers / lecturer users'],
                    ['label' => 'Content per user', 'value' => $this->formatAverage($totalContentItems, $totalUsers), 'note' => 'Projects + posts + papers per user'],
                    ['label' => 'Listings per partner', 'value' => $this->formatAverage($totalListings, $partnerCount), 'note' => 'Job + enrolment listings per partner account'],
                ],
            ],
        ];

        return view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'sectionKicker' => 'Administration',
            'users' => $users,
            'usersPayload' => $usersPayload,
            'oldForm' => $oldForm,
            'statsSections' => $statsSections,
        ]);
    }

    public function updateVerification(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'emailVerified' => ['nullable', 'boolean'],
            'isVerifiedCurrent' => ['nullable', 'boolean'],
            'isVerifiedAlumni' => ['nullable', 'boolean'],
            'isVerifiedLecturer' => ['nullable', 'boolean'],
            'isVerifiedPartner' => ['nullable', 'boolean'],
            'role_selection' => ['nullable', 'string', Rule::in(array_keys($this->roleSelectionMap()))],
        ]);

        $user->fill([
            'email_verified_at' => array_key_exists('emailVerified', $validated)
                ? ($request->boolean('emailVerified') ? ($user->email_verified_at ?? now()) : null)
                : $user->email_verified_at,
            'isVerifiedCurrent' => $request->boolean('isVerifiedCurrent'),
            'isVerifiedAlumni' => $request->boolean('isVerifiedAlumni'),
            'isVerifiedLecturer' => $request->boolean('isVerifiedLecturer'),
            'isVerifiedPartner' => $request->boolean('isVerifiedPartner'),
        ]);

        // If an admin wants to also change the declared role as part of verification,
        // accept an optional role_selection. Do not allow changing Admin users' roles.
        if (! empty($validated['role_selection']) && ! $user->hasRole('Admin')) {
            [$roleName, $roleFlags] = $this->resolveRolePayload($validated['role_selection']);
            $user->fill($roleFlags);
            Role::findOrCreate($roleName);
            $user->syncRoles([$roleName]);
        }

        $user->save();

        return back()->with('status', 'Verification updated for '.$user->name.'.');
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $requiresStudentId = in_array($validated['role_selection'], ['current', 'alumni'], true);

        [$roleName, $roleFlags] = $this->resolveRolePayload($validated['role_selection']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'password_confirmed_at' => now(),
            'student_id' => $requiresStudentId ? (int) $validated['student_id'] : null,
            'email_verified_at' => $request->boolean('emailVerified') ? now() : null,
            'isVerifiedCurrent' => $request->boolean('isVerifiedCurrent'),
            'isVerifiedAlumni' => $request->boolean('isVerifiedAlumni'),
            'isVerifiedLecturer' => $request->boolean('isVerifiedLecturer'),
            'isVerifiedPartner' => $request->boolean('isVerifiedPartner'),
        ] + $roleFlags);

        Role::findOrCreate($roleName);
        $user->assignRole($roleName);

        return back()->with('status', 'User created.');
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $requiresStudentId = in_array($validated['role_selection'], ['current', 'alumni'], true);

        [$roleName, $roleFlags] = $this->resolveRolePayload($validated['role_selection']);
        $isAdminUser = $user->hasRole('Admin');

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'student_id' => $requiresStudentId ? (int) $validated['student_id'] : null,
            'email_verified_at' => $request->boolean('emailVerified') ? ($user->email_verified_at ?? now()) : null,
            'isVerifiedCurrent' => $request->boolean('isVerifiedCurrent'),
            'isVerifiedAlumni' => $request->boolean('isVerifiedAlumni'),
            'isVerifiedLecturer' => $request->boolean('isVerifiedLecturer'),
            'isVerifiedPartner' => $request->boolean('isVerifiedPartner'),
        ];

        if (! $isAdminUser) {
            $attributes += $roleFlags;
        }

        $user->fill($attributes);

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
            $user->password_confirmed_at = now();
        }

        $user->save();

        if (! $isAdminUser) {
            Role::findOrCreate($roleName);
            $user->syncRoles([$roleName]);
        }

        return back()->with('status', 'User updated.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        $userName = $user->name;
        $user->delete();

        return back()->with('status', 'User deleted: '.$userName.'.');
    }

    private function roleSelectionMap(): array
    {
        return [
            'current' => ['role' => 'Current Student', 'flag' => 'isCurrent'],
            'alumni' => ['role' => 'Alumni', 'flag' => 'isAlumni'],
            'lecturer' => ['role' => 'Lecturer', 'flag' => 'isLecturer'],
            'partner' => ['role' => 'Partner', 'flag' => 'isPartner'],
            'general' => ['role' => 'General User', 'flag' => null],
            'admin' => ['role' => 'Admin', 'flag' => null],
        ];
    }

    private function resolveRolePayload(string $selection): array
    {
        $roleMap = $this->roleSelectionMap();
        $roleConfig = $roleMap[$selection] ?? $roleMap['general'];

        $roleFlags = [
            'isCurrent' => false,
            'isAlumni' => false,
            'isLecturer' => false,
            'isPartner' => false,
        ];

        if ($roleConfig['flag'] !== null) {
            $roleFlags[$roleConfig['flag']] = true;
        }

        return [$roleConfig['role'], $roleFlags];
    }

    private function formatPercentage(int $numerator, int $denominator): string
    {
        if ($denominator <= 0) {
            return '0.0%';
        }

        return number_format(($numerator / $denominator) * 100, 1).'%';
    }

    private function formatAverage(int $numerator, int $denominator): string
    {
        if ($denominator <= 0) {
            return '0.00';
        }

        return number_format($numerator / $denominator, 2);
    }

    private function effectiveRoleKey(User $user, array $roleMap): string
    {
        $roleName = $user->hasRole('Admin') ? 'Admin' : $user->getRoleNames()->first() ?? 'General User';

        return $roleMap[$roleName] ?? 'general';
    }
}
