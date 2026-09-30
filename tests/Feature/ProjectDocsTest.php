<?php

use App\Models\AccessToken;
use App\Models\Project;
use App\Models\ProjectDoc;
use App\Models\WorkItem;
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

    $created = Livewire::test('pages::projeto', ['slug' => $project->slug])
        ->set('docTitle', 'Módulo de Autenticação')
        ->set('docCategory', 'Auth')
        ->call('createDoc');

    $doc = ProjectDoc::firstOrFail();

    $created->assertRedirect(route('projetos.docs.show', ['project' => $project->id, 'doc' => $doc->id, 'editar' => 1]));

    Livewire::withQueryParams(['editar' => 1])->test('pages::doc', ['project' => $project->id, 'doc' => $doc->id])
        ->assertSet('editing', true);

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

test('the doc page shows a table of contents built from its headings', function () {
    $doc = ProjectDoc::factory()->create(['content' => "# Visão geral\n\n## Filas e Jobs\n"]);

    Livewire::test('pages::doc', ['project' => $doc->project_id, 'doc' => $doc->id])
        ->assertSee('Neste documento')
        ->assertSeeHtml('href="#visao-geral"')
        ->assertSeeHtml('href="#filas-e-jobs"');
});

test('the project page filters docs by title or category', function () {
    $project = Project::factory()->create();
    ProjectDoc::factory()->create(['project_id' => $project->id, 'title' => 'Arquitetura de Filas', 'category' => 'Infra']);
    ProjectDoc::factory()->create(['project_id' => $project->id, 'title' => 'Regras de Cobrança', 'category' => 'Financeiro']);

    Livewire::test('pages::projeto', ['slug' => $project->slug])
        ->assertSee('Regras de Cobrança')
        ->set('docFilter', 'filas')->assertSee('Arquitetura de Filas')->assertDontSee('Regras de Cobrança')
        ->set('docFilter', 'financeiro')->assertSee('Regras de Cobrança')->assertDontSee('Arquitetura de Filas');
});

test('the work item spec is rendered as markdown', function () {
    $item = WorkItem::factory()->create(['description' => '**negrito** aqui']);

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->assertSeeHtml('<strong>negrito</strong>');
});
