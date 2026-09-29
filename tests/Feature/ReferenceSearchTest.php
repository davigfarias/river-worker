<?php

use App\Actions\SearchCitations;
use App\Actions\SearchReferenceMaterials;
use App\Models\AccessToken;
use App\Models\Citation;
use App\Models\ReferenceMaterial;

beforeEach(function () {
    $this->token = AccessToken::factory()->create();
    $this->other = AccessToken::factory()->create();
});

test('works are searched by title and author and scoped to the token', function () {
    ReferenceMaterial::factory()->create(['access_token_id' => $this->token->id, 'title' => 'Refatoração', 'author' => 'Fowler']);
    ReferenceMaterial::factory()->create(['access_token_id' => $this->token->id, 'title' => 'Padrões', 'author' => 'Gamma']);
    ReferenceMaterial::factory()->create(['access_token_id' => $this->other->id, 'title' => 'Refatoração dos outros', 'author' => 'Fowler']);

    $results = app(SearchReferenceMaterials::class)->handle('Fowler', $this->token->id)->data;

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Refatoração');
});

test('citations are searched by quote text and scoped to the token', function () {
    $mine = ReferenceMaterial::factory()->create(['access_token_id' => $this->token->id]);
    Citation::factory()->create([
        'reference_material_id' => $mine->id,
        'access_token_id' => $this->token->id,
        'quote_text' => 'O acoplamento é o inimigo da mudança',
    ]);
    Citation::factory()->create([
        'reference_material_id' => $mine->id,
        'access_token_id' => $this->token->id,
        'quote_text' => 'A fé remove montanhas',
    ]);

    $foreign = ReferenceMaterial::factory()->create(['access_token_id' => $this->other->id]);
    Citation::factory()->create([
        'reference_material_id' => $foreign->id,
        'access_token_id' => $this->other->id,
        'quote_text' => 'O acoplamento cobre multidão de dívidas',
    ]);

    $results = app(SearchCitations::class)->handle('acoplamento', $this->token->id)->data;

    expect($results->total())->toBe(1)
        ->and($results->first()->quote_text)->toBe('O acoplamento é o inimigo da mudança');
});

test('an empty term yields no results', function () {
    ReferenceMaterial::factory()->create(['access_token_id' => $this->token->id]);

    expect(app(SearchReferenceMaterials::class)->handle('', $this->token->id)->data)->toHaveCount(0);
    expect(app(SearchCitations::class)->handle('   ', $this->token->id)->data->total())->toBe(0);
});
