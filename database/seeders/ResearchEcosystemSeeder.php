<?php

namespace Database\Seeders;

use App\Models\ResearchPaper;
use App\Models\ResearchPaperAuthor;
use App\Models\ResearchPaperProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResearchEcosystemSeeder extends Seeder
{
    public const RESEARCH_PAPER_COUNT = 60;

    public function run(): void
    {
        $userIds = User::query()->orderBy('id')->pluck('id')->values();

        if ($userIds->count() < 2) {
            return;
        }

        for ($i = 1; $i <= self::RESEARCH_PAPER_COUNT; $i++) {
            $title = $this->paperTitleFor($i);
            $doi = '10.2026/nmtafe.paper.' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);

            $paper = ResearchPaper::updateOrCreate(
                ['doi' => $doi],
                [
                    'title' => $title,
                    'abstract' => 'This paper explores practical pathways from study to industry contribution through applied collaboration and reflective evaluation.',
                    'pdf_url' => 'https://example.com/papers/' . $i . '.pdf',
                    'visibility' => $i % 5 === 0 ? 'private' : 'public',
                ]
            );

            $leadAuthorId = $userIds[($i + 2) % $userIds->count()];
            $coAuthorId = $userIds[($i + 17) % $userIds->count()];

            $paper->users()->syncWithoutDetaching([
                $leadAuthorId => ['author_order' => 1],
                $coAuthorId => ['author_order' => 2],
            ]);

            ResearchPaperProfile::updateOrCreate(
                ['research_paper_id' => $paper->id],
                [
                    'bio' => 'Profile for ' . $title . '.',
                    'details' => ['Methodology', 'Findings', 'Limitations', 'Future Work'],
                    'picture_url' => 'https://picsum.photos/seed/paper-' . $i . '/640/360',
                ]
            );
        }

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

    private function paperTitleFor(int $index): string
    {
        $subjects = [
            'Graduate Employability Pathways',
            'Mentorship in Vocational Learning',
            'Industry Collaboration Models',
            'Digital Portfolio Effectiveness',
            'Work-Integrated Learning Outcomes',
            'Peer-Led Technical Coaching',
        ];

        $angles = [
            'A Practice-Based Study',
            'An Applied Review',
            'A Community Evaluation',
            'Evidence from Alumni Networks',
            'A Longitudinal Snapshot',
        ];

        return $subjects[($index - 1) % count($subjects)]
            . ': '
            . $angles[((int) ceil($index / count($subjects)) - 1) % count($angles)];
    }
}
