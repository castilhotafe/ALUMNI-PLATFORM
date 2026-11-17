<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCollaborator;
use App\Models\ProjectProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectsEcosystemSeeder extends Seeder
{
    public const PROJECT_COUNT = 60;

    public function run(): void
    {
        $userIds = User::query()->orderBy('id')->pluck('id')->values();

        if ($userIds->count() < 2) {
            return;
        }

        for ($i = 1; $i <= self::PROJECT_COUNT; $i++) {
            $projectTitle = $this->projectTitleFor($i);

            $project = Project::create([
                'title' => $projectTitle,
                'description' => 'Applied team project focused on delivery quality, collaboration, and measurable outcomes for community stakeholders.',
                'repo_url' => 'https://github.com/nmtafe-alumni/project-' . $i,
                'visibility' => $i % 4 === 0 ? 'private' : 'public',
            ]);

            $ownerId = $userIds[($i - 1) % $userIds->count()];
            $contributorId = $userIds[($i + 11) % $userIds->count()];

            $project->users()->syncWithoutDetaching([
                $ownerId => ['contribution_role' => 'Owner'],
                $contributorId => ['contribution_role' => 'Contributor'],
            ]);

            ProjectProfile::create([
                'project_id' => $project->id,
                'bio' => 'Project profile for ' . $projectTitle . '.',
                'details' => ['Planning', 'Execution', 'Review', 'Iteration'],
                'picture_url' => 'https://picsum.photos/seed/project-' . $i . '/640/360',
            ]);
        }

        Project::with('users')->get()->each(function (Project $project): void {
            foreach ($project->users as $user) {
                ProjectCollaborator::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'name' => $user->name,
                    ],
                    [
                        'role' => $user->pivot->contribution_role,
                    ]
                );
            }
        });
    }

    private function projectTitleFor(int $index): string
    {
        $focuses = [
            'Alumni Mentorship Portal',
            'Work Placement Tracker',
            'Career Events Planner',
            'Skills Showcase Platform',
            'Industry Match Dashboard',
            'Graduate Outcomes Hub',
        ];

        $themes = [
            'Pilot',
            'Community Edition',
            'Collaboration Build',
            'Industry Stream',
            'Student Partnership',
        ];

        return $focuses[($index - 1) % count($focuses)]
            . ' - '
            . $themes[((int) ceil($index / count($focuses)) - 1) % count($themes)];
    }
}
