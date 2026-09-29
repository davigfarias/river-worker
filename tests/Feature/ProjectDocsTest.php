<?php

use App\Models\AccessToken;
use App\Models\Project;
use App\Models\ProjectDoc;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['access_token_id' => AccessToken::factory()->create()->id]);
});

test('a project is created with a unique slug', function () {
    Livewire::test('pages::projetos')->set('name', 'River Worker')->call('save')->assertHasNoErrors();
    Livewire::test('pages::projetos')->set('name', 'River Worker')->call('save')->assertHasNoErrors();

    expect(Project::pluck('slug')->all())->toBe(['river-worker', 'river-worker-2']);
});

test('the project name is required', function () {
    Livewire::test('pages::projetos')->call('save')->assertHasErrors('name');
});

test('a doc is created from the project page and edited with markdown', function () {
    $project = Project::factory()->create();

    Livewire::test('pages::projeto', ['slug' => $project->slug])
        ->set('docTitle', 'Módulo de Autenticação')
        ->set('docCategory', 'Auth')
        ->call('createDoc');

    $doc = ProjectDoc::firstOrFail();

    Livewire::test('pages::doc', ['project' => $project->id, 'doc' => $doc->id])
        ->set('content', "# Olá\n\n```php\necho 1;\n```")
        ->call('save')
        ->assertSet('editing', false);

    expect($doc->fresh()->content)->toContain('# Olá');
});

test('a doc is not reachable through another project', function () {
    $doc = ProjectDoc::factory()->create();

    $this->get(route('projetos.docs.show', ['project' => Project::factory()->create()->id, 'doc' => $doc->id]))->assertNotFound();
});
