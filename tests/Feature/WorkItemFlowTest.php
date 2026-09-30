<?php

use App\Actions\CreateWorkItem;
use App\Enums\ReadingStatus;
use App\Enums\WorkItemStatus;
use App\Models\AccessToken;
use App\Models\Project;
use App\Models\ProjectDoc;
use App\Models\ReferenceMaterial;
use App\Models\WorkFile;
use App\Models\WorkItem;
use Livewire\Livewire;

beforeEach(function () {
    $this->token = AccessToken::factory()->create();
    $this->withSession(['access_token_id' => $this->token->id]);
});

test('a work item is created with the fixed checklist', function () {
    $outcome = app(CreateWorkItem::class)->handle(['title' => 'Checkout com Strategy']);

    expect($outcome->success)->toBeTrue()
        ->and($outcome->data->status)->toBe(WorkItemStatus::Backlog)
        ->and($outcome->data->steps)->toHaveCount(count(config('worker.checklist')));
});

test('the dashboard creates a work item and redirects to it', function () {
    $project = Project::factory()->create();

    Livewire::test('pages::dashboard')
        ->set('title', 'Nova demanda')
        ->set('project_id', $project->id)
        ->call('createWorkItem')
        ->assertRedirect(route('trabalho.show', WorkItem::firstWhere('title', 'Nova demanda')->id));
});

test('the dashboard focus shows checklist progress and pending files', function () {
    $item = WorkItem::factory()->create(['status' => WorkItemStatus::InDevAi]);
    $item->steps()->createMany([['title' => 'a', 'is_completed' => true], ['title' => 'b']]);
    WorkFile::factory()->create(['work_item_id' => $item->id]);

    $data = Livewire::test('pages::dashboard')->instance()->dashboard;

    expect($data['focus']->id)->toBe($item->id)
        ->and($data['progress'])->toBe(50)
        ->and($data['pendingFiles'])->toBe(1);
});

test('steps and files can be toggled and removed on the work item page', function () {
    $item = WorkItem::factory()->create();
    $step = $item->steps()->create(['title' => 'x']);
    $file = WorkFile::factory()->create(['work_item_id' => $item->id]);

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->call('toggleStep', $step->id)
        ->call('toggleFile', $file->id)
        ->call('removeFile', $file->id);

    expect($step->fresh()->is_completed)->toBeTrue()
        ->and(WorkFile::count())->toBe(0);
});

test('a file is added with validation', function () {
    $item = WorkItem::factory()->create();

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->call('addFile')->assertHasErrors('filePath')
        ->set('filePath', 'app/Services/CheckoutService.php')
        ->set('reasonNotes', 'Extrair Strategy')
        ->call('addFile')->assertHasNoErrors();

    expect($item->files)->toHaveCount(1);
});

test('the lifecycle timeline moves the work item between stages', function () {
    $item = WorkItem::factory()->create();

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->call('changeStatus', 'testing');

    expect($item->fresh()->status)->toBe(WorkItemStatus::Testing);
});

test('the timeline cannot mark a work item as deployed without the deploy button', function () {
    $item = WorkItem::factory()->create();

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->call('changeStatus', 'deployed');

    expect($item->fresh()->status)->toBe(WorkItemStatus::Backlog)
        ->and($item->fresh()->deployed_at)->toBeNull();
});

test('registering a deploy stamps deployed_at and the status', function () {
    $item = WorkItem::factory()->create();

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->set('deployVersion', 'v1.0.0')
        ->call('registerDeploy');

    $item->refresh();

    expect($item->status)->toBe(WorkItemStatus::Deployed)
        ->and($item->deployed_at)->not->toBeNull()
        ->and($item->deploy_version)->toBe('v1.0.0');
});

test('a step of another work item cannot be toggled through this page', function () {
    $item = WorkItem::factory()->create();
    $foreign = WorkItem::factory()->create()->steps()->create(['title' => 'y']);

    Livewire::test('pages::trabalho', ['id' => $item->id])->call('toggleStep', $foreign->id);

    expect($foreign->fresh()->is_completed)->toBeFalse();
});

test('the dashboard links to every area', function () {
    $html = Livewire::test('pages::dashboard')->html();

    foreach (['projetos', 'principios', 'estudos', 'conceitos', 'referencias', 'busca'] as $route) {
        expect($html)->toContain(route($route));
    }
});

test('the deploy step is the last item of the timeline and opens the confirmation modal', function () {
    $item = WorkItem::factory()->create(['status' => WorkItemStatus::ReadyDeploy]);

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->assertSeeHtmlInOrder(['Pronto para deploy', 'Em produção', 'aria-label="Fazer deploy"'])
        ->assertSee('Confirmar deploy?');
});

test('the dashboard preselects the active project and links the new work item to it', function () {
    $project = Project::factory()->create();
    WorkItem::factory()->create(['project_id' => $project->id]);

    Livewire::test('pages::dashboard')
        ->assertSet('project_id', $project->id)
        ->set('title', 'Vinculada')
        ->call('createWorkItem');

    expect(WorkItem::firstWhere('title', 'Vinculada')->project_id)->toBe($project->id);
});

test('a work item created from the project page is linked to that project', function () {
    $project = Project::factory()->create();

    Livewire::test('pages::projeto', ['slug' => $project->slug])
        ->set('workItemTitle', 'Da página do projeto')
        ->call('createWorkItem');

    expect($project->workItems()->pluck('title')->all())->toBe(['Da página do projeto']);
});

test('the spec is edited inline and saved as markdown', function () {
    $item = WorkItem::factory()->create(['description' => 'antiga']);

    Livewire::test('pages::trabalho', ['id' => $item->id])
        ->set('editingSpec', true)
        ->set('description', '## Nova spec')
        ->call('saveSpec')
        ->assertSet('editingSpec', false)
        ->assertSeeHtml('<h2>Nova spec</h2>');

    expect($item->fresh()->description)->toBe('## Nova spec');
});

test('the dashboard summarises the pipeline, reading and recent docs', function () {
    $project = Project::factory()->create();
    WorkItem::factory()->count(2)->create(['project_id' => $project->id, 'status' => WorkItemStatus::Backlog]);
    WorkItem::factory()->create(['project_id' => $project->id, 'status' => WorkItemStatus::Deployed, 'deployed_at' => now()]);
    WorkItem::factory()->create(['project_id' => Project::factory()->create()->id, 'status' => WorkItemStatus::Backlog, 'updated_at' => now()->subDay()]);
    $doc = ProjectDoc::factory()->create(['title' => 'Arquitetura de Filas']);
    ReferenceMaterial::factory()->create(['title' => 'Refatoração', 'reading_status' => ReadingStatus::Reading]);

    $data = Livewire::test('pages::dashboard')->instance()->dashboard;

    expect($data['pipeline']['backlog'])->toBe(2)
        ->and($data['pipeline']['deployed'])->toBe(1)
        ->and($data['weekDeploys'])->toBe(1);

    Livewire::test('pages::dashboard')->assertSee('Arquitetura de Filas')->assertSee('Refatoração');
});
