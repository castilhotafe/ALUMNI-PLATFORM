<?php

namespace Tests\Feature;

use App\Http\Controllers\PaperController;
use App\Http\Requests\StorePaperRequest;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

// Pest does not set this PHPUnit subprocess bootstrap constant itself.
if (! defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', dirname(__DIR__, 2).'/vendor/autoload.php');
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class PaperControllerScopeTest extends TestCase
{
    use RefreshDatabase;

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
}
