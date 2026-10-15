<?php

namespace Tests\Feature;

use App\Http\Controllers\PaperController;
use App\Http\Requests\StorePaperRequest;
use App\Http\Requests\UpdatePaperRequest;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;


// Pest does not set this PHPUnit subprocess bootstrap constant itself.
if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', dirname(__DIR__, 2) . '/vendor/autoload.php');
}
uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up authenticated environment and bypass CSRF checks
    $this->user = User::factory()->create(['name' => 'Dr. Jane Doe']);
    $this->actingAs($this->user);
    $this->withoutMiddleware(PreventRequestForgery::class);
    $this->withoutExceptionHandling();
});

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PaperControllerScopeTest extends TestCase
{
    private function paperRequest(string $class, array $payload, User $user): FormRequest
    {
        // Isolate controller behavior while the Form Requests from #42 are pending.
        // Separate processes prevent these test-only aliases leaking into other tests.
        if (! class_exists($class)) {
            class_alias(FormRequest::class, $class);
        }

        $request = Mockery::mock($class);
        $request->shouldReceive('validated')->once()->andReturn($payload);
        $request->shouldReceive('user')->andReturn($user);

        return $request;
    }

    public function test_store_attaches_lead_author_and_creates_coauthors_and_profile(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Jane Doe']);
        $request = $this->paperRequest(StorePaperRequest::class, [
            'title' => 'Quantum Computing',
            'abstract' => 'Modern computing architectures.',
            'doi' => '10.1000/quantum',
            'pdf_url' => 'https://example.com/paper.pdf',
            'visibility' => 'public',
            'authors' => [
                ['name' => 'Professor Smith', 'order' => 2],
                ['name' => ''],
            ],
            'profile_bio' => 'Extended summary.',
            'profile_details' => ['Physics', ''],
            'picture_url' => 'https://example.com/paper.png',
        ], $user);

        $response = (new PaperController)->store($request);
        $paper = ResearchPaper::sole();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Research paper created.', session('status_paper'));
        $this->assertSame('10.1000/quantum', $paper->doi);
        $this->assertSame('https://example.com/paper.pdf', $paper->pdf_url);
        $this->assertSame($user->id, $paper->users()->sole()->id);
        $this->assertSame(1, $paper->users()->sole()->pivot->author_order);
        $this->assertSame(['Dr. Jane Doe', 'Professor Smith'], $paper->authors()->orderBy('author_order')->pluck('name')->all());
        $this->assertSame([1, 2], $paper->authors()->orderBy('author_order')->pluck('author_order')->all());
        $this->assertSame('Extended summary.', $paper->profile->bio);
        $this->assertSame(['Physics'], $paper->profile->details);
        $this->assertSame('https://example.com/paper.png', $paper->profile->picture_url);
    }

    public function test_store_does_not_duplicate_owner_or_create_an_empty_profile(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Jane Doe']);
        $request = $this->paperRequest(StorePaperRequest::class, [
            'title' => 'Private Notes',
            'abstract' => 'Unpublished results.',
            'visibility' => 'private',
            'authors' => [['name' => 'dr. jane doe', 'order' => 1]],
            'profile_details' => ['', null],
        ], $user);

        (new PaperController)->store($request);
        $paper = ResearchPaper::sole();

        $this->assertSame('private', $paper->visibility);
        $this->assertSame(1, $paper->authors()->count());
        $this->assertSame('dr. jane doe', $paper->authors()->sole()->name);
        $this->assertNull($paper->profile);
    }

    public function test_update_replaces_coauthors_and_updates_existing_profile(): void
    {
        $user = User::factory()->create(['name' => 'Dr. Jane Doe']);
        $paper = ResearchPaper::factory()->create();
        $paper->users()->attach($user->id, ['author_order' => 1]);
        $paper->authors()->create(['name' => 'Old Author', 'author_order' => 2]);
        $paper->profile()->create(['bio' => 'Old bio', 'details' => ['Chemistry']]);
        $request = $this->paperRequest(UpdatePaperRequest::class, [
            'title' => 'Updated Title',
            'abstract' => 'Updated abstract.',
            'visibility' => 'private',
            'authors' => [['name' => 'New Author', 'order' => 3], ['name' => '']],
            'profile_bio' => 'New bio',
            'profile_details' => ['Biology', ''],
        ], $user);

        $response = (new PaperController)->update($request, $paper);
        $paper->refresh();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Research paper updated.', session('status'));
        $this->assertSame('Updated Title', $paper->title);
        $this->assertSame('Updated abstract.', $paper->abstract);
        $this->assertSame('private', $paper->visibility);
        $this->assertSame(['Dr. Jane Doe', 'New Author'], $paper->authors()->orderBy('author_order')->pluck('name')->all());
        $this->assertSame([1, 3], $paper->authors()->orderBy('author_order')->pluck('author_order')->all());
        $this->assertSame('New bio', $paper->profile->bio);
        $this->assertSame(['Biology'], $paper->profile->details);
        $this->assertSame(1, $paper->users()->sole()->pivot->author_order);
    }

    public function test_update_creates_optional_profile_when_it_is_missing(): void
    {
        $user = User::factory()->create();
        $paper = ResearchPaper::factory()->create();
        $paper->users()->attach($user->id, ['author_order' => 1]);
        $request = $this->paperRequest(UpdatePaperRequest::class, [
            'title' => 'Updated Paper',
            'abstract' => 'Updated abstract.',
            'visibility' => 'public',
            'picture_url' => 'https://example.com/paper.png',
        ], $user);

        (new PaperController)->update($request, $paper);

        $this->assertSame('https://example.com/paper.png', $paper->fresh()->profile->picture_url);
        $this->assertSame([], $paper->fresh()->profile->details);
        $this->assertSame($user->name, $paper->authors()->sole()->name);
    }

    public function test_destroy_deletes_paper_and_related_records(): void
    {
        $user = User::factory()->create();
        $paper = ResearchPaper::factory()->create();
        $paper->users()->attach($user->id, ['author_order' => 1]);
        $paper->authors()->create(['name' => $user->name, 'author_order' => 1]);
        $paper->profile()->create(['bio' => 'Paper details.']);

        $response = (new PaperController)->destroy($paper);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Research paper deleted.', session('status'));
        $this->assertDatabaseMissing('research_papers', ['id' => $paper->id]);
        $this->assertDatabaseMissing('user_papers', ['research_paper_id' => $paper->id]);
        $this->assertDatabaseMissing('research_paper_authors', ['research_paper_id' => $paper->id]);
        $this->assertDatabaseMissing('research_paper_profiles', ['research_paper_id' => $paper->id]);
    }
}

/*
|--------------------------------------------------------------------------
| Store Method Tests
|--------------------------------------------------------------------------
*/

it('stores a research paper with correct author orders and profiles', function () {
    $payload = [
        'title' => 'Quantum Computing Implementations',
        'abstract' => 'This paper explores modern architectures.',
        'doi' => '10.1000/xyz123',
        'pdf_url' => 'https://example.edu',
        'visibility' => 'public',
        'authors' => [
            ['name' => 'Professor Smith', 'order' => 2],
            ['name' => '', 'order' => 3], // Should be skipped completely
        ],
        'profile_bio' => 'Lab notes and extended summary.',
        'profile_details' => ['Physics', 'Quantum', ''], // Empty details string should be stripped
        'picture_url' => 'https://example.edu',
    ];

    $response = $this->post(route('papers.store'), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('status_paper', 'Research paper created.');

    $this->assertDatabaseHas('research_papers', [
        'title' => 'Quantum Computing Implementations',
        'doi' => '10.1000/xyz123',
    ]);

    $paper = ResearchPaper::first();

    // Verify creator is attached to pivot table with author_order 1
    expect($paper->users->contains($this->user))->toBeTrue()
        ->and($paper->users()->first()->pivot->author_order)->toBe(1);

    // Verify both authors exist (Jane Doe automatically added as first author if not typed in payload)
    expect($paper->authors)->toHaveCount(2);
    $this->assertDatabaseHas('research_paper_authors', [
        'research_paper_id' => $paper->id,
        'name' => 'Dr. Jane Doe',
        'author_order' => 1,
    ]);
    $this->assertDatabaseHas('research_paper_authors', [
        'research_paper_id' => $paper->id,
        'name' => 'Professor Smith',
        'author_order' => 2,
    ]);

    // Profile asserts
    expect($paper->profile->details)->toEqual(['Physics', 'Quantum']);
});
