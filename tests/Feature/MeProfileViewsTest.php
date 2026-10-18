<?php

use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

// These are Blade rendering tests, not endpoint or browser interaction tests.
// Profile routes belong to a separate issue; PostController is tracked separately.
beforeEach(function () {
    $this->withoutVite();
    $this->withSession([]);
    // Direct rendering bypasses HTTP session middleware used by old().
    $this->app['request']->setLaravelSession($this->app['session']->driver());
    View::share('errors', new ViewErrorBag);

    foreach ([
        ['PATCH', '/_profile-view-tests/me/profile', 'me.profile.update'],
        ['GET', '/_profile-view-tests/profile', 'profile.edit'],
        ['PATCH', '/_profile-view-tests/profile', 'profile.update'],
        ['DELETE', '/_profile-view-tests/profile', 'profile.destroy'],
        ['POST', '/_profile-view-tests/posts', 'posts.store'],
        ['PATCH', '/_profile-view-tests/posts/{post}', 'posts.update'],
        ['DELETE', '/_profile-view-tests/posts/{post}', 'posts.destroy'],
    ] as [$method, $uri, $name]) {
        if (! Route::has($name)) {
            // Only URL generation is needed. Never serve production behavior here.
            Route::match([$method], $uri, fn () => abort(501))->name($name);
        }
    }

    Route::getRoutes()->refreshNameLookups();
});

function meProfileViewUser(): User
{
    return User::factory()->create([
        'name' => 'Ada Alumni',
        'email' => 'ada@example.test',
    ])->setRelations([
        'profile' => null,
        'posts' => new Collection,
        'projects' => new Collection,
        'researchPapers' => new Collection,
    ]);
}

function meProfileViewDom(string $html): DOMXPath
{
    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="UTF-8">'.$html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}

function meProfileViewHasClass(DOMXPath $dom, string $id, string $class): bool
{
    return $dom->query('//*[@id="'.$id.'" and contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]')->length === 1;
}

it('renders the complete Me view with empty profile and contribution states', function () {
    $user = meProfileViewUser();
    $this->actingAs($user);

    $view = $this->view('platform.sections.profile', ['user' => $user, 'pageTitle' => 'Me']);
    $dom = meProfileViewDom((string) $view);

    $view->assertSeeText('Manage your profile and activity.')
        ->assertSeeText('Ada Alumni')
        ->assertSeeText('ada@example.test')
        ->assertSeeText('Add a professional summary and highlight your details.')
        ->assertSeeText('No details added yet')
        ->assertSeeText('Your contributions')
        ->assertSeeText('Account Settings');

    $text = $dom->evaluate('normalize-space(//main)');
    expect($text)->toContain('No posts yet. Use + Post to add your first post.', 'No projects linked yet.', 'No papers linked yet.')
        ->and($dom->query('//span[@class="badge text-bg-light border" and text()="0"]')->length)->toBe(3)
        ->and($dom->query('//img[contains(@alt, "profile picture")]')->length)->toBe(0)
        ->and($dom->evaluate('string(//*[@id="profileEditForm"]/@style)'))->toBe('display: none;')
        ->and($dom->evaluate('string(//*[@id="profileEditForm"]//form/@action)'))->toBe(route('me.profile.update'))
        ->and($dom->evaluate('string(//*[@id="profileEditForm"]//input[@name="_method"]/@value)'))->toBe('PATCH');

    foreach (['Post' => 'posts.store', 'Project' => 'projects.store', 'Paper' => 'papers.store'] as $panel => $route) {
        expect(meProfileViewHasClass($dom, 'collapseNew'.$panel, 'show'))->toBeFalse()
            ->and($dom->evaluate('string(//*[@id="collapseNew'.$panel.'"]//form/@action)'))->toBe(route($route))
            ->and($dom->query('//*[@id="collapseNew'.$panel.'"]//input[@name="_token"]')->length)->toBe(1);
    }
});

it('renders Me through the existing platform section dispatch', function () {
    $user = meProfileViewUser();
    $this->actingAs($user);

    $view = $this->view('platform.section', ['section' => 'me', 'pageTitle' => 'Me', 'user' => $user]);
    $dom = meProfileViewDom((string) $view);

    $view->assertSeeText('Manage your profile and activity.')->assertSeeText('Your contributions');
    expect($dom->query('//*[@id="profileDisplay"]')->length)->toBe(1)
        ->and($dom->query('//*[@id="collapseNewPost"]')->length)->toBe(1);
});

it('renders supplied profile details and escaped contribution records with edit and delete forms', function () {
    $user = meProfileViewUser();
    // In-memory view data deliberately does not imply that resume_link is persisted.
    $profile = new Profile([
        'bio' => 'Engineer <script>alert("bio")</script>',
        'details' => ['Laravel & PHP', 'Mentor'],
        'picture_url' => 'https://example.test/ada.png',
    ]);
    $profile->setAttribute('resume_link', 'https://example.test/resume.pdf');
    $post = (new Post(['title' => 'Post <script>unsafe</script>', 'body' => 'Post body', 'visibility' => 'private']))->forceFill(['id' => 11]);
    $project = (new Project(['title' => 'Community project', 'description' => 'Project description', 'repo_url' => 'https://example.test/repo', 'visibility' => 'private']))->forceFill(['id' => 12]);
    $project->setRelation('pivot', new Pivot(['contribution_role' => 'Maintainer']));
    $paper = (new ResearchPaper(['title' => 'Alumni research', 'abstract' => 'Paper abstract', 'doi' => '10.1234/alumni', 'pdf_url' => 'https://example.test/paper.pdf', 'visibility' => 'private']))->forceFill(['id' => 13]);
    $paper->setRelation('pivot', new Pivot(['author_order' => 2]));
    $user->setRelations(['profile' => $profile, 'posts' => new Collection([$post]), 'projects' => new Collection([$project]), 'researchPapers' => new Collection([$paper])]);
    $this->actingAs($user);

    $view = $this->view('platform.sections.profile', ['user' => $user]);
    $dom = meProfileViewDom((string) $view);

    $view->assertSee('Engineer &lt;script&gt;', false)
        ->assertSee('Post &lt;script&gt;unsafe&lt;/script&gt;', false)
        ->assertDontSee('<script>unsafe</script>', false)
        ->assertSeeText('Laravel & PHP')
        ->assertSeeText('Maintainer')
        ->assertSeeText('Alumni research');
    expect($dom->evaluate('string(//img[contains(@alt, "profile picture")]/@src)'))->toBe($profile->picture_url)
        ->and($dom->evaluate('string(//a[normalize-space(.)="View Resume"]/@href)'))->toBe($profile->resume_link)
        ->and($dom->query('//*[@id="detailsList"]//input[@name="details[]"]')->length)->toBe(2)
        ->and($dom->query('//span[@class="badge text-bg-light border" and text()="1"]')->length)->toBe(3)
        ->and($dom->evaluate('normalize-space(//main)'))->toContain('Author order: 2');

    foreach ([['Post', 'posts', $post], ['Project', 'projects', $project], ['Paper', 'papers', $paper]] as [$panel, $resource, $record]) {
        $form = '//*[@id="collapseEdit'.$panel.$record->id.'"]//form';
        expect($dom->evaluate('string('.$form.'/@action)'))->toBe(route($resource.'.update', $record))
            ->and($dom->evaluate('string('.$form.'//input[@name="_method"]/@value)'))->toBe('PATCH')
            ->and($dom->evaluate('string('.$form.'//input[@name="title"]/@value)'))->toBe($record->title)
            ->and($dom->query($form.'//option[@value="private" and @selected]')->length)->toBe(1)
            ->and($dom->query('//form[@action="'.route($resource.'.destroy', $record).'"]//input[@name="_method" and @value="DELETE"]')->length)->toBe(1);
    }
});

it('opens only the matching contribution create panel for a named error bag', function (string $bag, string $panel) {
    $user = meProfileViewUser();
    $this->actingAs($user);
    $errors = (new ViewErrorBag)->put($bag, new MessageBag(['title' => $bag.' title is required.']));

    $dom = meProfileViewDom((string) $this->view('platform.sections.profile', compact('user', 'errors')));

    foreach (['Post', 'Project', 'Paper'] as $candidate) {
        expect(meProfileViewHasClass($dom, 'collapseNew'.$candidate, 'show'))->toBe($candidate === $panel);
    }

    expect($dom->evaluate('normalize-space(//*[@id="collapseNew'.$panel.'"])'))->toContain($bag.' title is required.');
})->with(['post' => ['post', 'Post'], 'project' => ['project', 'Project'], 'paper' => ['paper', 'Paper']]);

it('opens the matching contribution create panel for its success flash', function (string $bag, string $panel) {
    $user = meProfileViewUser();
    $this->actingAs($user)->withSession(['status_'.$bag => $bag.' saved.']);

    $dom = meProfileViewDom((string) $this->view('platform.sections.profile', compact('user')));

    foreach (['Post', 'Project', 'Paper'] as $candidate) {
        expect(meProfileViewHasClass($dom, 'collapseNew'.$candidate, 'show'))->toBe($candidate === $panel);
    }

    expect($dom->evaluate('normalize-space(//*[@id="collapseNew'.$panel.'"])'))->toContain($bag.' saved.');
})->with(['post' => ['post', 'Post'], 'project' => ['project', 'Project'], 'paper' => ['paper', 'Paper']]);

it('shares old input between contribution create forms', function () {
    $user = meProfileViewUser();
    $this->actingAs($user)->withSession(['_old_input' => [
        'title' => 'Unsaved title', 'visibility' => 'private', 'body' => 'Unsaved body',
        'description' => 'Unsaved description', 'repo_url' => 'https://example.test/draft-repo',
        'abstract' => 'Unsaved abstract', 'doi' => '10.1234/draft', 'pdf_url' => 'https://example.test/draft.pdf',
    ]]);

    $dom = meProfileViewDom((string) $this->view('platform.sections.profile', compact('user')));

    // Error bags are separate, but old title/visibility values are shared by the create forms.
    foreach (['Post', 'Project', 'Paper'] as $panel) {
        $form = '//*[@id="collapseNew'.$panel.'"]//form';
        expect($dom->evaluate('string('.$form.'//input[@name="title"]/@value)'))->toBe('Unsaved title')
            ->and($dom->query($form.'//option[@value="private" and @selected]')->length)->toBe(1);
    }

    foreach (['body' => 'Unsaved body', 'description' => 'Unsaved description', 'abstract' => 'Unsaved abstract', 'doi' => '10.1234/draft', 'pdf_url' => 'https://example.test/draft.pdf'] as $field => $value) {
        expect($dom->evaluate('string(//textarea[@name="'.$field.'"])'))->toBe($value);
    }
    expect($dom->evaluate('string(//input[@name="repo_url"]/@value)'))->toBe('https://example.test/draft-repo');
});

it('retains stored profile and inline edit values after failed input', function () {
    $user = meProfileViewUser();
    $user->setRelation('profile', new Profile(['bio' => 'Stored summary', 'details' => ['Stored detail'], 'picture_url' => 'https://example.test/stored.png']));
    $post = (new Post(['title' => 'Stored post', 'body' => 'Stored body', 'visibility' => 'public']))->forceFill(['id' => 11]);
    $user->setRelation('posts', new Collection([$post]));
    $this->actingAs($user)->withSession(['_old_input' => ['bio' => 'Rejected summary', 'details' => ['Rejected detail'], 'picture_url' => 'https://example.test/rejected.png', 'title' => 'Rejected title', 'body' => 'Rejected body', 'visibility' => 'private']]);
    $errors = (new ViewErrorBag)->put('default', new MessageBag(['bio' => 'The biography is too long.', 'details.0' => 'The detail is too long.']))
        ->put('post', new MessageBag(['title' => 'The post title is invalid.']));

    $dom = meProfileViewDom((string) $this->view('platform.sections.profile', compact('user', 'errors')));

    expect($dom->evaluate('string(//textarea[@name="bio"])'))->toBe('Stored summary')
        ->and($dom->evaluate('string(//*[@id="detailsList"]//input/@value)'))->toBe('Stored detail')
        ->and($dom->evaluate('string(//input[@name="picture_url"]/@value)'))->toBe('https://example.test/stored.png')
        ->and($dom->evaluate('string(//*[@id="profileEditForm"]/@style)'))->toBe('display: none;')
        ->and($dom->evaluate('normalize-space(//*[@id="profileEditForm"])'))->toContain('The biography is too long.', 'The detail is too long.')
        ->and($dom->evaluate('string(//*[@id="collapseEditPost11"]//input[@name="title"]/@value)'))->toBe('Stored post')
        ->and($dom->evaluate('string(//*[@id="collapseEditPost11"]//textarea[@name="body"])'))->toBe('Stored body')
        ->and(meProfileViewHasClass($dom, 'collapseEditPost11', 'show'))->toBeFalse()
        ->and(meProfileViewHasClass($dom, 'collapseNewPost', 'show'))->toBeTrue();
});

it('displays a persisted resume link in the profile and edit form', function () {
    $user = meProfileViewUser();
    $profile = $user->profile()->create(['bio' => 'Stored profile', 'resume_link' => 'https://example.test/stored-resume.pdf']);
    $user->setRelation('profile', $profile);
    $this->actingAs($user);

    $dom = meProfileViewDom((string) $this->view('platform.sections.profile', compact('user')));

    expect($profile->fresh()->resume_link)->toBe('https://example.test/stored-resume.pdf')
        ->and($dom->evaluate('string(//a[normalize-space(.)="View Resume"]/@href)'))->toBe($profile->resume_link)
        ->and($dom->evaluate('string(//input[@name="resume_link"]/@value)'))->toBe($profile->resume_link);
});

it('renders account settings and all three partials through their support components', function () {
    $user = meProfileViewUser();
    $this->actingAs($user);

    $view = $this->view('profile.edit', compact('user'));
    $dom = meProfileViewDom((string) $view);

    $view->assertSeeText('Manage your profile and security.')
        ->assertSeeText('Profile Information')->assertSeeText('Update Password')->assertSeeText('Delete Account')
        ->assertSeeText('Go back to Dashboard');

    foreach (['profile.update' => 'patch', 'password.update' => 'put', 'profile.destroy' => 'delete'] as $route => $method) {
        $form = '//form[@action="'.route($route).'" and .//input[@name="_method" and @value="'.$method.'"]]';
        expect($dom->evaluate('string('.$form.'//input[@name="_method"]/@value)'))->toBe($method)
            ->and($dom->query($form.'//input[@name="_token"]')->length)->toBe(1);
    }

    expect($dom->evaluate('string(//form[@id="send-verification"]/@action)'))->toBe(route('verification.send'))
        ->and($dom->evaluate('string(//input[@id="name"]/@value)'))->toBe('Ada Alumni')
        ->and($dom->evaluate('string(//input[@id="email"]/@value)'))->toBe('ada@example.test')
        ->and($dom->query('//label[@for="name" and contains(@class, "form-label")]')->length)->toBe(1)
        ->and($dom->query('//input[@id="name" and contains(@class, "form-control")]')->length)->toBe(1)
        ->and($dom->query('//button[@type="submit" and contains(@class, "btn-danger") and normalize-space(.)="Save"]')->length)->toBe(2)
        ->and($dom->query('//button[@type="button" and contains(@class, "btn-outline-secondary") and normalize-space(.)="Cancel"]')->length)->toBe(1)
        ->and($dom->evaluate('string(//div[@*[name()="data-x-on:close.stop"]]/@style)'))->toBe('display: none;');
});

it('uses account old input and isolates default, password and deletion error bags', function () {
    $user = meProfileViewUser();
    $this->actingAs($user)->withSession(['_old_input' => ['name' => 'Draft <Name>', 'email' => 'draft@example.test', 'password' => 'must-not-render']]);
    $errors = (new ViewErrorBag)->put('default', new MessageBag(['name' => 'Account name error.', 'email' => 'Account email error.']))
        ->put('updatePassword', new MessageBag(['current_password' => 'Current password error.', 'password' => 'New password error.', 'password_confirmation' => 'Confirmation error.']))
        ->put('userDeletion', new MessageBag(['password' => 'Deletion password error.']));

    $view = $this->view('profile.edit', compact('user', 'errors'));
    $dom = meProfileViewDom((string) $view);

    expect($dom->evaluate('string(//input[@id="name"]/@value)'))->toBe('Draft <Name>')
        ->and($dom->evaluate('string(//input[@id="email"]/@value)'))->toBe('draft@example.test')
        ->and($dom->evaluate('normalize-space(//form[@action="'.route('profile.update').'" and .//input[@name="_method" and @value="patch"]])'))->toContain('Account name error.', 'Account email error.')
        ->and($dom->evaluate('normalize-space(//form[@action="'.route('password.update').'"])'))->toContain('Current password error.', 'New password error.', 'Confirmation error.')
        ->and($dom->evaluate('normalize-space(//form[@action="'.route('password.update').'"])'))->not->toContain('Deletion password error.')
        ->and($dom->evaluate('normalize-space(//form[@action="'.route('profile.destroy').'" and .//input[@name="_method" and @value="delete"]])'))->toContain('Deletion password error.')
        ->and($dom->evaluate('normalize-space(//form[@action="'.route('profile.destroy').'" and .//input[@name="_method" and @value="delete"]])'))->not->toContain('New password error.')
        ->and($dom->query('//div[contains(@class, "invalid-feedback")]')->length)->toBe(6)
        ->and($dom->evaluate('string(//div[@*[name()="data-x-on:close.stop"]]/@style)'))->toBe('display: block;');
    $view->assertDontSee('must-not-render');
});

it('renders the existing account saved-state tokens without treating the controller message as a token', function (string $status, int $inlineMessages) {
    $user = meProfileViewUser();
    $this->actingAs($user)->withSession(['status' => $status]);

    $dom = meProfileViewDom((string) $this->view('profile.edit', compact('user')));

    expect($dom->query('//p[normalize-space(.)="Saved."]')->length)->toBe($inlineMessages);
})->with(['profile token' => ['profile-updated', 1], 'password token' => ['password-updated', 1], 'current profile controller message' => ['Profile updated.', 0]]);

it('keeps the verification reminder conditional on the MustVerifyEmail contract', function () {
    $user = meProfileViewUser();
    $user->email_verified_at = null;
    $this->actingAs($user)->withSession(['status' => 'verification-link-sent']);

    // The current User intentionally does not implement MustVerifyEmail.
    $this->view('profile.edit', compact('user'))
        ->assertDontSeeText('Your email address is unverified.')
        ->assertDontSeeText('A new verification link has been sent to your email address.');
});
