<?php

use App\Actions\GetReadingNote;
use App\Actions\GetReferenceReviewAgenda;
use App\Actions\RecordReadingNoteReview;
use App\DTO\ReferenceReviewAgendaDTO;
use App\Models\ReadingNote;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public int $referenceMaterialId;

    public bool $showReviewModal = false;

    #[Locked]
    public ?int $readingNoteIdUnderReview = null;

    public function mount(int $referenceMaterialId): void
    {
        $this->referenceMaterialId = $referenceMaterialId;
    }

    #[Computed]
    public function agenda(): ReferenceReviewAgendaDTO
    {
        return app(GetReferenceReviewAgenda::class)
            ->handle($this->referenceMaterialId, (int) session('access_token_id'))
            ->data;
    }

    #[Computed]
    public function readingNoteUnderReview(): ?ReadingNote
    {
        if ($this->readingNoteIdUnderReview === null) {
            return null;
        }

        return app(GetReadingNote::class)
            ->handle($this->readingNoteIdUnderReview, (int) session('access_token_id'))
            ->data;
    }

    public function openReview(int $readingNoteId): void
    {
        if (! $this->isInQueue($readingNoteId)) {
            Flux::toast(duration: 2500, text: 'Esta anotação não está na fila de revisão de hoje.', variant: 'warning');

            return;
        }

        $this->readingNoteIdUnderReview = $readingNoteId;
        $this->showReviewModal = true;

        unset($this->readingNoteUnderReview);
    }

    /**
     * Autoavaliação: lembrou sobe um degrau da escada, não lembrou volta ao primeiro.
     */
    public function grade(bool $recalled, RecordReadingNoteReview $action): void
    {
        if ($this->readingNoteIdUnderReview === null) {
            return;
        }

        $outcome = $action->handle($this->readingNoteIdUnderReview, (int) session('access_token_id'), $recalled);

        if (! $outcome->success) {
            Flux::toast(duration: 2500, heading: 'Ocorreu um erro', text: $outcome->message, variant: 'danger');

            return;
        }

        Flux::toast(duration: 2500, text: $outcome->message, variant: $recalled ? 'success' : 'warning');

        $this->nextNote();
    }

    /**
     * Puxa a próxima anotação devida sem fechar a modal.
     */
    public function nextNote(): void
    {
        unset($this->agenda, $this->readingNoteUnderReview);

        $next = $this->agenda->due->first();

        if ($next === null) {
            $this->closeReview();
            Flux::toast(duration: 2500, heading: 'Revisões concluídas', text: 'A fila de revisão desta referência está vazia.', variant: 'success');

            return;
        }

        $this->readingNoteIdUnderReview = $next->id;

        unset($this->readingNoteUnderReview);
    }

    public function closeReview(): void
    {
        $this->showReviewModal = false;
        $this->readingNoteIdUnderReview = null;

        unset($this->readingNoteUnderReview);
    }

    public function updatedShowReviewModal(): void
    {
        if (! $this->showReviewModal) {
            $this->closeReview();
        }
    }

    private function isInQueue(int $readingNoteId): bool
    {
        return $this->agenda->due->contains(fn ($review): bool => $review->id === $readingNoteId);
    }
};
