<?php

use App\Http\Controllers\PlatformController;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

it('lists and filters public papers using the prototype data contract', function (array $query, array $expectedTitles) {
    $realUser = User::factory()->create();
    $user = Mockery::mock(User::class)->makePartial();
    $user->setRawAttributes($realUser->getAttributes(), true);
    // Test-only stand-in for the inverse relationship missing from the current dev.
    $user->shouldReceive('researchPapers')->once()->andReturn(
        $realUser->belongsToMany(ResearchPaper::class, 'user_papers')
            ->withPivot('author_order')->withTimestamps()
    );

    $quantum = ResearchPaper::factory()->create(['title' => 'Quantum Paper', 'abstract' => 'Computing research.', 'visibility' => 'public']);
    $quantum->authors()->create(['name' => 'Professor Smith', 'author_order' => 2]);
    $quantum->profile()->create(['bio' => 'Lab notes.', 'details' => ['Physics', 'Quantum']]);
    ResearchPaper::factory()->create(['title' => 'Biology Paper', 'abstract' => 'Cell research.', 'visibility' => 'public']);
    ResearchPaper::factory()->create(['title' => 'Private Paper', 'visibility' => 'private']);

    $request = Request::create('/papers', 'GET', $query);
    $request->setUserResolver(fn () => $user);
    $data = [];
    $view = Mockery::mock(View::class);
    // The paper views belong to #44; test the controller's view data, not rendering.
    $this->mock(Factory::class)->shouldReceive('make')->once()
        ->with('platform.section', Mockery::on(function (array $values) use (&$data) {
            $data = $values;

            return true;
        }), [])->andReturn($view);

    expect((new PlatformController)->papers($request))->toBe($view)
        ->and($data['section'])->toBe('papers')
        ->and($data['pageTitle'])->toBe('Research Papers')
        ->and($data['papers']->pluck('title')->sort()->values()->all())->toBe($expectedTitles)
        ->and($data['userPapers'])->toHaveCount(0)
        ->and($data['detailOptions'])->toBe(['Physics', 'Quantum'])
        ->and($data['searchQuery'])->toBe(trim($query['q'] ?? ''));
})->with([
    'public listing hides private papers' => [[], ['Biology Paper', 'Quantum Paper']],
    'case-insensitive author search' => [['q' => '  PROFESSOR SMITH  '], ['Quantum Paper']],
    'matching detail filters' => [['details' => ['physics', 'Quantum']], ['Quantum Paper']],
    'all selected details must match' => [['details' => ['Physics', 'Biology']], []],
]);
