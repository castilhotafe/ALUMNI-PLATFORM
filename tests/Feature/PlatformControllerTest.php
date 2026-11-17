<?php

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
