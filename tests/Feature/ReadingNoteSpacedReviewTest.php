<?php

use App\Actions\GetReferenceReviewAgenda;
use App\Actions\RecordReadingNoteReview;
use App\DTO\ReferenceReviewAgendaDTO;
use App\Models\AccessToken;
use App\Models\ReadingNote;
use App\Models\ReferenceMaterial;
use App\Models\ReviewLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

const RN_TODAY = '2026-09-17';

beforeEach(function () {
    Carbon::setTestNow(RN_TODAY);
    CarbonImmutable::setTestNow(RN_TODAY);

    $this->token = AccessToken::factory()->create();
    $this->withSession(['access_token_id' => $this->token->id]);

    $this->material = ReferenceMaterial::factory()->create([
        'access_token_id' => $this->token->id,
        'title' => 'Arquitetura Limpa',
    ]);

    Livewire::withoutLazyLoading();
});

afterEach(function () {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
});

function dueReadingNote(ReferenceMaterial $material, string $dueAt, int $stage = 1, array $attributes = []): ReadingNote
{
    return ReadingNote::factory()
        ->dueOn($dueAt, $stage)
        ->create([
            'reference_material_id' => $material->id,
            'access_token_id' => test()->token->id,
            ...$attributes,
        ]);
}

function referenceAgenda(int $referenceMaterialId): ReferenceReviewAgendaDTO
{
    return app(GetReferenceReviewAgenda::class)
        ->handle($referenceMaterialId, test()->token->id)
        ->data;
}

test('criar uma anotação agenda a primeira cobrança para amanhã', function () {
    Livewire::test('pages::referencia', ['id' => $this->material->id])
        ->set('readingNoteForm.body', 'Uma reflexão sobre a obra.')
        ->call('addReadingNote');

    $note = ReadingNote::where('reference_material_id', $this->material->id)->sole();

    expect($note->review_stage)->toBe(1)
        ->and($note->next_review_at->toDateString())->toBe('2026-09-18');
});

test('a fila da referência traz as anotações vencidas hoje', function () {
    dueReadingNote($this->material, RN_TODAY, attributes: ['title' => 'Sobre acoplamento']);

    $agenda = referenceAgenda($this->material->id);

    expect($agenda->due->pluck('title')->all())->toBe(['Sobre acoplamento'])
        ->and($agenda->totalDue)->toBe(1);
});

test('anotações de outra referência não aparecem na fila', function () {
    $other = ReferenceMaterial::factory()->create(['access_token_id' => $this->token->id]);
    dueReadingNote($other, RN_TODAY);

    expect(referenceAgenda($this->material->id)->due)->toBeEmpty();
});

test('anotações de outro token nunca aparecem na fila', function () {
    $foreignMaterial = ReferenceMaterial::factory()->create([
        'access_token_id' => AccessToken::factory()->create()->id,
    ]);

    ReadingNote::factory()->dueOn(RN_TODAY)->create([
        'reference_material_id' => $foreignMaterial->id,
    ]);

    expect(referenceAgenda($foreignMaterial->id)->due)->toBeEmpty();
});

test('uma anotação consolidada não volta para a fila', function () {
    ReadingNote::factory()->consolidated()->create([
        'reference_material_id' => $this->material->id,
        'access_token_id' => $this->token->id,
    ]);

    expect(referenceAgenda($this->material->id)->due)->toBeEmpty();
});

test('lembrei sobe um degrau e agenda a cobrança em dias corridos', function () {
    $note = dueReadingNote($this->material, RN_TODAY);

    $outcome = app(RecordReadingNoteReview::class)->handle($note->id, $this->token->id, recalled: true);

    expect($outcome->success)->toBeTrue();

    $note->refresh();

    expect($note->review_stage)->toBe(2)
        ->and($note->next_review_at->toDateString())->toBe('2026-09-19')
        ->and($note->consolidated_at)->toBeNull();
});

test('travei devolve a anotação ao primeiro degrau', function () {
    $note = dueReadingNote($this->material, RN_TODAY, stage: 3);

    app(RecordReadingNoteReview::class)->handle($note->id, $this->token->id, recalled: false);

    $note->refresh();

    expect($note->review_stage)->toBe(1)
        ->and($note->next_review_at->toDateString())->toBe('2026-09-18');
});

test('a quarta revisão bem sucedida consolida a anotação e a tira da fila', function () {
    $note = dueReadingNote($this->material, RN_TODAY, stage: 4);

    app(RecordReadingNoteReview::class)->handle($note->id, $this->token->id, recalled: true);

    $note->refresh();

    expect($note->review_stage)->toBe(5)
        ->and($note->next_review_at)->toBeNull()
        ->and($note->consolidated_at)->not->toBeNull()
        ->and(referenceAgenda($this->material->id)->due)->toBeEmpty();
});

test('cada revisão deixa um registro polimórfico no histórico', function () {
    $note = dueReadingNote($this->material, RN_TODAY, stage: 2);

    app(RecordReadingNoteReview::class)->handle($note->id, $this->token->id, recalled: true);

    $log = ReviewLog::first();

    expect($log->reviewable_type)->toBe($note->getMorphClass())
        ->and($log->reviewable_id)->toBe($note->id)
        ->and($log->recalled)->toBeTrue()
        ->and($log->stage_before)->toBe(2)
        ->and($log->stage_after)->toBe(3);
});

test('uma anotação de outro token não pode ser revisada', function () {
    $foreignMaterial = ReferenceMaterial::factory()->create([
        'access_token_id' => AccessToken::factory()->create()->id,
    ]);
    $note = ReadingNote::factory()->dueOn(RN_TODAY)->create([
        'reference_material_id' => $foreignMaterial->id,
    ]);

    $outcome = app(RecordReadingNoteReview::class)->handle($note->id, $this->token->id, recalled: true);

    expect($outcome->success)->toBeFalse()
        ->and($note->refresh()->review_stage)->toBe(1);
});

test('o componente mostra os cartões da fila da referência', function () {
    dueReadingNote($this->material, RN_TODAY, attributes: ['title' => 'Sobre acoplamento']);

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->assertSee('Sobre acoplamento')
        ->assertSee('1ª revisão');
});

test('a modal abre com o corpo da anotação para autoavaliação', function () {
    $note = dueReadingNote($this->material, RN_TODAY, attributes: [
        'title' => 'Sobre acoplamento',
        'body' => 'O acoplamento precede a dívida técnica.',
    ]);

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->call('openReview', $note->id)
        ->assertSet('showReviewModal', true)
        ->assertSee('O acoplamento precede a dívida técnica.')
        ->assertSee('Lembrei');
});

test('lembrei sobe a anotação um degrau e registra a revisão', function () {
    $note = dueReadingNote($this->material, RN_TODAY);

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->call('openReview', $note->id)
        ->call('grade', true);

    expect($note->refresh()->review_stage)->toBe(2)
        ->and($note->next_review_at->toDateString())->toBe('2026-09-19');
});

test('não lembro devolve a anotação ao primeiro degrau e registra a revisão', function () {
    $note = dueReadingNote($this->material, RN_TODAY);

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->call('openReview', $note->id)
        ->call('grade', false);

    expect($note->refresh()->review_stage)->toBe(1)
        ->and(ReviewLog::where('reviewable_id', $note->id)->sole()->recalled)->toBeFalse();
});

test('uma anotação que não está na fila de hoje não pode ser aberta', function () {
    $note = dueReadingNote($this->material, '2026-09-20');

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->call('openReview', $note->id)
        ->assertSet('showReviewModal', false)
        ->assertSet('readingNoteIdUnderReview', null);
});

test('avaliar puxa a próxima anotação da fila sem fechar a modal', function () {
    $first = dueReadingNote($this->material, '2026-09-10', attributes: ['title' => 'Primeira']);
    $second = dueReadingNote($this->material, RN_TODAY, attributes: ['title' => 'Segunda']);

    Livewire::test('revisoes-da-referencia', ['referenceMaterialId' => $this->material->id])
        ->call('openReview', $first->id)
        ->call('grade', false)
        ->assertSet('showReviewModal', true)
        ->assertSet('readingNoteIdUnderReview', $second->id);
});
