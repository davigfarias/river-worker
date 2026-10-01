<?php

use App\Models\AccessToken;
use App\Models\BragEntry;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['access_token_id' => AccessToken::factory()->create()->id]);
});

test('a brag entry is created, edited and deleted', function () {
    Livewire::test('pages::brag-document')
        ->call('create')
        ->set('form.goal', 'Reduzir tempo de deploy')->set('form.impact_faster', 'De 20 para 5 min')
        ->call('save')->assertHasNoErrors();

    $entry = BragEntry::firstOrFail();
    expect($entry->impact_faster)->toBe('De 20 para 5 min');

    Livewire::test('pages::brag-document')
        ->call('edit', $entry->id)->set('form.project', 'CI')->call('save')->assertHasNoErrors()
        ->call('delete', $entry->id);

    expect(BragEntry::count())->toBe(0);
});

test('brag entry goal is required', function () {
    Livewire::test('pages::brag-document')->call('create')->call('save')->assertHasErrors(['form.goal']);
});

test('a saved entry shows its answers in the view panel', function () {
    $entry = BragEntry::factory()->create(['impact_faster' => 'De 20 para 5 min']);

    Livewire::test('pages::brag-document')->call('show', $entry->id)->assertSee('O que ficou mais rápido?')->assertSee('De 20 para 5 min');
});
