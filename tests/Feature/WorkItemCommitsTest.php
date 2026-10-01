<?php

use App\Actions\ListRepositoryCommits;
use App\Models\AccessToken;
use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['access_token_id' => AccessToken::factory()->create()->id]);
    config(['cache.stores.file.driver' => 'array']);
});

function fakeGithubCommit(string $sha, string $message): array
{
    return [
        'sha' => $sha,
        'html_url' => "https://github.com/acme/app/commit/{$sha}",
        'author' => ['login' => 'dave'],
        'commit' => ['message' => $message, 'author' => ['name' => 'Dave', 'date' => '2026-10-01T12:00:00Z']],
    ];
}

test('the github repository is extracted from the project url', function (?string $url, ?string $expected) {
    expect(Project::factory()->make(['repository_url' => $url])->githubRepository())->toBe($expected);
})->with([
    ['https://github.com/acme/app', 'acme/app'],
    ['https://github.com/acme/my.app.git', 'acme/my.app'],
    ['https://github.com/acme/app/', 'acme/app'],
    ['https://gitlab.com/acme/app', null],
    [null, null],
]);

test('commits are listed from the github api', function () {
    Http::fake(['api.github.com/repos/acme/app/commits*' => Http::response([fakeGithubCommit('abc1234def', "feat: login\n\ncorpo")])]);

    $outcome = app(ListRepositoryCommits::class)->handle(Project::factory()->create(['repository_url' => 'https://github.com/acme/app']));

    expect($outcome->success)->toBeTrue()
        ->and($outcome->data[0])->toMatchArray(['sha' => 'abc1234def', 'message' => 'feat: login', 'author' => 'dave']);
});

test('a private repository without token fails with a helpful message', function () {
    Http::fake(['api.github.com/*' => Http::response([], 404)]);

    $outcome = app(ListRepositoryCommits::class)->handle(Project::factory()->create(['repository_url' => 'https://github.com/acme/secret']));

    expect($outcome->success)->toBeFalse()->and($outcome->message)->toContain('GITHUB_TOKEN');
});

test('the project page shows the repository commits', function () {
    Http::fake(['api.github.com/*' => Http::response([fakeGithubCommit('abc1234def', 'feat: login')])]);
    $project = Project::factory()->create(['repository_url' => 'https://github.com/acme/app']);

    Livewire::test('pages::projeto', ['slug' => $project->slug])
        ->assertSee('feat: login')
        ->assertSee('abc1234');
});
