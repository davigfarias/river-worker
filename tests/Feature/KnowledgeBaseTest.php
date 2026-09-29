<?php

use App\Actions\ReorderCodeStudyStep;
use App\Actions\SearchGlobal;
use App\Models\AccessToken;
use App\Models\CodeStudy;
use App\Models\CodeStudyStep;
use App\Models\Concept;
use App\Models\Principle;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['access_token_id' => AccessToken::factory()->create()->id]);
});

test('a principle is created, filtered and deleted', function () {
    Livewire::test('pages::principios')
        ->set('name', 'Single Responsibility')->set('acronym', 'SRP')->set('summary', 'Uma razão para mudar')
        ->call('save')->assertHasNoErrors();

    $principle = Principle::firstOrFail();

    Livewire::test('pages::principios')
        ->set('filter', 'SRP')->assertSee('Uma razão para mudar')
        ->set('filter', 'zzz')->assertDontSee('Uma razão para mudar')
        ->call('delete', $principle->id);

    expect(Principle::count())->toBe(0);
});

test('principle name and summary are required', function () {
    Livewire::test('pages::principios')->call('save')->assertHasErrors(['name', 'summary']);
});

test('a concept can be linked to a principle', function () {
    $principle = Principle::factory()->create();

    Livewire::test('pages::conceitos')
        ->set('title', 'Inversão de dependência')->set('definition', 'Depender de abstrações')->set('principle_id', $principle->id)
        ->call('save')->assertHasNoErrors();

    expect(Concept::first()->principle->is($principle))->toBeTrue();
});

test('study steps are reordered by position', function () {
    $study = CodeStudy::factory()->create();
    [$a, $b, $c] = collect(range(0, 2))->map(fn (int $i) => CodeStudyStep::factory()->create(['code_study_id' => $study->id, 'position' => $i]))->all();

    app(ReorderCodeStudyStep::class)->handle($study->id, $c->id, 0);

    expect($study->steps()->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);
});

test('a study step can be created, edited and removed', function () {
    $study = CodeStudy::factory()->create();

    $component = Livewire::test('pages::estudo', ['id' => $study->id])
        ->call('newStep')
        ->set('stepTitle', 'Passo 1')->set('stepSnippet', '<?php echo 1;')->set('stepMarkdown', '**nota**')
        ->call('saveStep')->assertHasNoErrors();

    $step = $study->steps()->firstOrFail();

    $component->call('editStep', $step->id)->set('stepTitle', 'Passo 1b')->call('saveStep');
    expect($step->fresh()->title)->toBe('Passo 1b');

    $component->call('removeStep', $step->id);
    expect($study->steps()->count())->toBe(0);
});

test('global search finds docs, principles and studies', function () {
    Principle::factory()->create(['name' => 'Dependency Inversion', 'acronym' => 'DIP']);
    CodeStudy::factory()->create(['title' => 'Refatorando Strategy']);

    $results = app(SearchGlobal::class)->handle('Strategy', 1)->data;

    expect($results->pluck('title')->all())->toContain('Refatorando Strategy');
});

test('a principle keeps its category, shows it as a badge and filters by it', function () {
    Livewire::test('pages::principios')
        ->set('name', 'Open/Closed')->set('acronym', 'OCP')->set('category', 'SOLID')->set('summary', 'Aberto para extensão')
        ->call('save')->assertHasNoErrors();

    expect(Principle::firstOrFail()->category)->toBe('SOLID');

    Livewire::test('pages::principios')
        ->assertSee('SOLID')
        ->set('filter', 'SOLID')->assertSee('Open/Closed')
        ->set('filter', 'Arquitetura')->assertDontSee('Aberto para extensão');
});
