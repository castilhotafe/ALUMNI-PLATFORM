<?php

use App\Models\ResearchPaper;
use App\Models\ResearchPaperProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ResearchPaperProfile Model Relationships & Casts', function () {

    it('belongs to a ResearchPaper model', function () {
        $paper = ResearchPaper::factory()->create();
        $profile = ResearchPaperProfile::create([
            'research_paper_id' => $paper->id,
            'bio' => 'Peer-reviewed study abstract notes',
            'details' => ['AI', 'Machine Learning'],
        ]);

        expect($profile->researchPaper)->toBeInstanceOf(ResearchPaper::class)
            ->and($profile->researchPaper->id)->toBe($paper->id);
    });

    it('casts details attribute to an array', function () {
        $paper = ResearchPaper::factory()->create();
        $profile = ResearchPaperProfile::create([
            'research_paper_id' => $paper->id,
            'details' => ['Data Science', 'Python'],
        ]);

        expect($profile->fresh()->details)->toBeArray()
            ->toBe(['Data Science', 'Python']);
    });
});
