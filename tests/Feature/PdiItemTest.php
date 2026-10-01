<?php

use App\Models\AccessToken;
use App\Models\PdiItem;
use Livewire\Livewire;

beforeEach(function () {
    $this->withSession(['access_token_id' => AccessToken::factory()->create()->id]);
});

test('a pdi item is created, edited and deleted', function () {
    Livewire::test('pages::pdi')
        ->call('create')
        ->set('form.objective', 'Aprender Kubernetes')->set('form.action', 'Fazer curso')
        ->call('save')->assertHasNoErrors();

    $item = PdiItem::firstOrFail();
    expect($item->action)->toBe('Fazer curso');

    Livewire::test('pages::pdi')
        ->call('edit', $item->id)->set('form.deadline', 'Dez/2026')->call('save')->assertHasNoErrors()
        ->call('delete', $item->id);

    expect(PdiItem::count())->toBe(0);
});

test('pdi objective is required', function () {
    Livewire::test('pages::pdi')->call('create')->call('save')->assertHasErrors(['form.objective']);
});

test('a saved item shows its answers in the view panel', function () {
    $item = PdiItem::factory()->create(['measurement' => 'Certificação CKA']);

    Livewire::test('pages::pdi')->call('show', $item->id)->assertSee('Como vou medir?')->assertSee('Certificação CKA');
});
