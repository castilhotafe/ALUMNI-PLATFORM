<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCollaborator;
use App\Models\ResearchPaper;
use App\Models\ResearchPaperAuthor;
use Illuminate\Database\Seeder;

class CollaboratorsSeeders extends Seeder
{
    public function run(): void
    {
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

        ResearchPaper::with('users')->get()->each(function (ResearchPaper $paper): void {
            foreach ($paper->users as $user) {
                ResearchPaperAuthor::firstOrCreate(
                    [
                        'research_paper_id' => $paper->id,
                        'name' => $user->name,
                    ],
                    [
                        'author_order' => $user->pivot->author_order ?? 1,
                    ]
                );
            }
        });
    }
}
