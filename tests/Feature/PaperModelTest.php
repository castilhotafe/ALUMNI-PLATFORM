<?php

use App\Models\ResearchPaper;
use App\Models\ResearchPaperAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Inverse Relationships Structural Testing
|--------------------------------------------------------------------------
*/

it('belongs to a research paper from an author profile record tracking frame', function () {
    $paper = ResearchPaper::factory()->create(['title' => 'Deep Learning Analytics']);

    $authorRelationRecord = ResearchPaperAuthor::create([
        'research_paper_id' => $paper->id,
        'name' => 'Alice Watson',
        'author_order' => 1,
    ]);

    // Exercises the researchPaper() relationship function execution path
    expect($authorRelationRecord->researchPaper)->toBeInstanceOf(ResearchPaper::class)
        ->and($authorRelationRecord->researchPaper->id)->toBe($paper->id);
});

/*
|--------------------------------------------------------------------------
| Model Query Scopes (visibleTo) Verification Checks
|--------------------------------------------------------------------------
*/

it('hides private documents when a guest visitor views papers via visibleTo query scopes', function () {
    // Arrange
    ResearchPaper::factory()->create(['visibility' => 'public']);
    ResearchPaper::factory()->create(['visibility' => 'private']);

    // Act: Invoke scope with a fallback context null user
    $papersCollection = ResearchPaper::visibleTo(null)->get();

    // Assert
    expect($papersCollection)->toHaveCount(1)
        ->and($papersCollection->first()->visibility)->toBe('public');
});

it('restricts private documents to their respective matching authors only', function () {
    // Setup distinct user testing configurations
    $researcherAlpha = User::factory()->create();
    $researcherBeta = User::factory()->create();

    $sharedPublicWork = ResearchPaper::factory()->create(['visibility' => 'public']);

    // Alpha writes a private paper
    $alphaSecretWork = ResearchPaper::factory()->create(['visibility' => 'private']);
    $alphaSecretWork->users()->attach($researcherAlpha->id, ['author_order' => 1]);

    // Beta writes a private paper
    $betaSecretWork = ResearchPaper::factory()->create(['visibility' => 'private']);
    $betaSecretWork->users()->attach($researcherBeta->id, ['author_order' => 1]);

    // Query Scope Execution Testing for Researcher Alpha
    $alphaQueryScopeResults = ResearchPaper::visibleTo($researcherAlpha)->get();

    expect($alphaQueryScopeResults)->toHaveCount(2)
        ->and($alphaQueryScopeResults->pluck('id'))->toContain($sharedPublicWork->id, $alphaSecretWork->id)
        ->and($alphaQueryScopeResults->pluck('id'))->not->toContain($betaSecretWork->id);
});
