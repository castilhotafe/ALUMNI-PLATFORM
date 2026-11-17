<?php

use App\Models\EnrolmentListing;
use App\Models\Group;
use App\Models\JobListing;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed required Spatie roles for directory and networking queries
    foreach (['Current Student', 'Alumni', 'Lecturer', 'Partner', 'General User', 'Admin'] as $roleName) {
        Role::findOrCreate($roleName);
    }
    Permission::findOrCreate('create job listings');
    Permission::findOrCreate('create enrolment listings');

    $this->withoutMiddleware(PreventRequestForgery::class);
});

describe('PlatformController Access Guardrails', function () {

    it('redirects unauthenticated guests to the login page for all platform sections', function (string $route) {
        get($route)->assertRedirect(route('login'));
    })->with([
        '/projects',
        '/papers',
        '/me',
        '/directory',
        '/networking-hub',
        '/discover-hub',
    ]);
});

describe('Projects Section (PlatformController@projects)', function () {

    it('renders the projects showcase section for authenticated users', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $project = Project::factory()->create(['visibility' => 'public']);

        actingAs($user)
            ->get(route('projects'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'projects',
                'pageTitle' => 'Projects',
                'sectionKicker' => 'Collaboration',
            ])
            ->assertViewHas('projects', fn ($projects) => $projects->contains($project));
    });

    it('filters projects by search query and selected profile details', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $matchedProject = Project::factory()->create([
            'title' => 'Laravel Alumni System',
            'description' => 'A web platform for graduates.',
            'visibility' => 'public',
        ]);
        $matchedProject->profile()->create([
            'bio' => 'Full-stack application',
            'details' => ['PHP', 'Vue.js'],
        ]);

        $unmatchedProject = Project::factory()->create([
            'title' => 'Python Data Science Script',
            'description' => 'Unrelated project.',
            'visibility' => 'public',
        ]);

        actingAs($user)
            ->get(route('projects', ['q' => 'Laravel', 'details' => ['PHP']]))
            ->assertOk()
            ->assertViewHas('projects', function ($projects) use ($matchedProject, $unmatchedProject) {
                return $projects->contains($matchedProject) && ! $projects->contains($unmatchedProject);
            })
            ->assertViewHas('detailOptions', fn ($options) => in_array('PHP', $options, true));
    });
});

describe('Research Papers Section (PlatformController@papers)', function () {

    it('renders the research papers section for authenticated users', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $paper = ResearchPaper::factory()->create(['visibility' => 'public']);

        actingAs($user)
            ->get(route('papers'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'papers',
                'pageTitle' => 'Research Papers',
                'sectionKicker' => 'Knowledge sharing',
            ])
            ->assertViewHas('papers', fn ($papers) => $papers->contains($paper));
    });

    it('filters research papers by keyword search query and profile details', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $matchedPaper = ResearchPaper::factory()->create([
            'title' => 'Artificial Intelligence in Higher Education',
            'abstract' => 'Exploring neural network applications.',
            'doi' => '10.1000/182',
            'visibility' => 'public',
        ]);
        $matchedPaper->profile()->create([
            'details' => ['Machine Learning', 'AI'],
        ]);

        $unmatchedPaper = ResearchPaper::factory()->create([
            'title' => 'Database Optimization Strategies',
            'abstract' => 'SQL performance tuning.',
            'visibility' => 'public',
        ]);

        actingAs($user)
            ->get(route('papers', ['q' => 'Intelligence', 'details' => ['Machine Learning']]))
            ->assertOk()
            ->assertViewHas('papers', function ($papers) use ($matchedPaper, $unmatchedPaper) {
                return $papers->contains($matchedPaper) && ! $papers->contains($unmatchedPaper);
            });
    });
});

describe('Personal Workspace Section (PlatformController@me)', function () {

    it('renders the me workspace with eager-loaded user relations', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        actingAs($user)
            ->get(route('me'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'me',
                'pageTitle' => 'Me',
                'sectionKicker' => 'Personal hub',
            ])
            ->assertViewHas('user', fn ($viewUser) => $viewUser->id === $user->id);
    });
});

describe('Developer Directory Section (PlatformController@directory)', function () {

    it('renders the developer directory filtered by community roles', function () {
        $currentUser = User::factory()->create(['name' => 'Alex Alumni']);
        $currentUser->assignRole('Alumni');

        $otherUser = User::factory()->create(['name' => 'Sam Student']);
        $otherUser->assignRole('Current Student');

        actingAs($currentUser)
            ->get(route('directory'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'directory',
                'pageTitle' => 'Developer Directory',
                'sectionKicker' => 'Find alumni',
            ])
            ->assertViewHas('users', fn ($users) => $users->contains($currentUser) && $users->contains($otherUser));
    });

    it('filters directory members by search query and skill detail options', function () {
        $currentUser = User::factory()->create(['name' => 'Lead Developer']);
        $currentUser->assignRole('Alumni');
        $currentUser->profile()->create([
            'bio' => 'Experienced software engineer',
            'details' => ['Laravel', 'Tailwind'],
        ]);

        $otherUser = User::factory()->create(['name' => 'Graphic Designer']);
        $otherUser->assignRole('General User');

        actingAs($currentUser)
            ->get(route('directory', ['q' => 'Lead', 'details' => ['Laravel']]))
            ->assertOk()
            ->assertViewHas('users', function ($users) use ($currentUser, $otherUser) {
                return $users->contains($currentUser) && ! $users->contains($otherUser);
            });
    });
});

describe('Networking Hub Section (PlatformController@networking)', function () {

    it('renders the networking hub displaying public and member groups', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $group = Group::factory()->create([
            'creator_user_id' => $user->id,
            'name' => 'Web Dev Mentorship Circle',
            'description' => 'A community group for web developers.',
        ]);
        $group->members()->attach($user->id, ['member_role' => 'owner']);

        actingAs($user)
            ->get(route('networking'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'networking',
                'pageTitle' => 'Networking Hub',
                'sectionKicker' => 'Communities',
            ])
            ->assertViewHas('groups', fn ($groups) => $groups->contains($group))
            ->assertViewHas('memberGroups', fn ($memberGroups) => $memberGroups->contains($group));
    });

    it('filters networking groups by title, description, or attached member names', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $matchedGroup = Group::factory()->create([
            'creator_user_id' => $user->id,
            'name' => 'Cybersecurity Guild',
            'description' => 'Ethical hacking and network defense.',
        ]);

        $unmatchedGroup = Group::factory()->create([
            'creator_user_id' => $user->id,
            'name' => 'UI/UX Designers',
            'description' => 'Figma workflows.',
        ]);

        actingAs($user)
            ->get(route('networking', ['q' => 'Cybersecurity']))
            ->assertOk()
            ->assertViewHas('groups', function ($groups) use ($matchedGroup, $unmatchedGroup) {
                return $groups->contains($matchedGroup) && ! $groups->contains($unmatchedGroup);
            });
    });
});

describe('Discover Hub Section (PlatformController@discover)', function () {

    it('renders the discover hub with paginated job listings and enrolment listings for authenticated users', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $jobListing = JobListing::factory()->create(['title' => 'Senior Backend Engineer']);
        $enrolmentListing = EnrolmentListing::factory()->create(['course_title' => 'Diploma of IT']);

        actingAs($user)
            ->get(route('discover'))
            ->assertOk()
            ->assertViewIs('platform.section')
            ->assertViewHasAll([
                'section' => 'discover',
                'pageTitle' => 'Discover Hub',
                'sectionKicker' => 'Opportunities',
            ])
            ->assertViewHas('jobListings', fn ($jobs) => $jobs->getCollection()->contains($jobListing))
            ->assertViewHas('enrolmentListings', fn ($enrolments) => $enrolments->getCollection()->contains($enrolmentListing));
    });

    it('populates manageable listings filtered by creator for listing managers', function () {
        $partnerUser = User::factory()->create([
            'isPartner' => true,
            'isVerifiedPartner' => true,
        ]);
        $partnerUser->givePermissionTo('create job listings');
        $partnerUser->givePermissionTo('create enrolment listings');

        $otherUser = User::factory()->create();

        $ownJob = JobListing::factory()->create([
            'creator_user_id' => $partnerUser->id,
            'title' => 'Own Company Job',
        ]);
        $otherJob = JobListing::factory()->create([
            'creator_user_id' => $otherUser->id,
            'title' => 'Other Company Job',
        ]);

        $ownEnrolment = EnrolmentListing::factory()->create([
            'creator_user_id' => $partnerUser->id,
            'course_title' => 'Own Course',
        ]);
        $otherEnrolment = EnrolmentListing::factory()->create([
            'creator_user_id' => $otherUser->id,
            'course_title' => 'Other Course',
        ]);

        actingAs($partnerUser)
            ->get(route('discover'))
            ->assertOk()
            ->assertViewHas('canPostListings', true)
            ->assertViewHas('manageableJobListings', function ($jobs) use ($ownJob, $otherJob) {
                return $jobs->contains($ownJob) && ! $jobs->contains($otherJob);
            })
            ->assertViewHas('manageableEnrolmentListings', function ($enrolments) use ($ownEnrolment, $otherEnrolment) {
                return $enrolments->contains($ownEnrolment) && ! $enrolments->contains($otherEnrolment);
            });
    });

    it('allows Admin users to see all listings in manageable collections', function () {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $admin->givePermissionTo('create job listings');
        $admin->givePermissionTo('create enrolment listings');

        $job1 = JobListing::factory()->create();
        $job2 = JobListing::factory()->create();

        actingAs($admin)
            ->get(route('discover'))
            ->assertOk()
            ->assertViewHas('canPostListings', true)
            ->assertViewHas('manageableJobListings', function ($jobs) use ($job1, $job2) {
                return $jobs->contains($job1) && $jobs->contains($job2);
            });
    });

    it('filters discover listings by search query and detail options', function () {
        $user = User::factory()->create();
        $user->assignRole('Alumni');

        $matchedJob = JobListing::factory()->create(['title' => 'Cloud Architect']);
        $matchedJob->profile()->create([
            'bio' => 'AWS infrastructure role',
            'details' => ['DevOps', 'AWS'],
        ]);

        $unmatchedJob = JobListing::factory()->create(['title' => 'Frontend Developer']);

        actingAs($user)
            ->get(route('discover', ['q' => 'Cloud', 'details' => ['DevOps']]))
            ->assertOk()
            ->assertViewHas('jobListings', function ($jobs) use ($matchedJob, $unmatchedJob) {
                return $jobs->getCollection()->contains($matchedJob)
                    && ! $jobs->getCollection()->contains($unmatchedJob);
            })
            ->assertViewHas('detailOptions', fn ($options) => in_array('DevOps', $options, true));
    });
});
