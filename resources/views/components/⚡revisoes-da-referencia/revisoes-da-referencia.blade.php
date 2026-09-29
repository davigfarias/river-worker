<div>
    @php($agenda = $this->agenda)

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <flux:heading size="sm" class="text-on-surface-variant uppercase">Revisão espaçada</flux:heading>
        <flux:badge size="sm" variant="pill" :color="$agenda->isEmpty() ? 'zinc' : 'amber'">
            {{ $agenda->totalDue }} pendente{{ $agenda->totalDue === 1 ? '' : 's' }} hoje
        </flux:badge>
    </div>

    @if (! $agenda->isEmpty())
        @if ($agenda->hiddenCount() > 0)
            <flux:text size="sm" class="text-on-surface-variant mb-3 block">
                Mostrando {{ $agenda->due->count() }}, mais {{ $agenda->hiddenCount() }} aguardando.
            </flux:text>
        @endif

        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            @foreach ($agenda->due as $review)
                <button
                    type="button"
                    wire:key="due-reading-note-{{ $review->id }}"
                    wire:click="openReview({{ $review->id }})"
                    class="text-left"
                    aria-label="Revisar {{ $review->title }}"
                >
                    <flux:card size="sm" class="hover:border-primary/50 flex h-full flex-col justify-between transition-colors">
                        <flux:heading size="sm" class="line-clamp-2">{{ $review->title }}</flux:heading>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <flux:badge size="sm" variant="pill">{{ $review->stage_label }}</flux:badge>

                            <flux:badge
                                size="sm"
                                variant="pill"
                                :color="$review->isOverdue() ? 'red' : 'zinc'"
                            >
                                {{ $review->overdueLabel() }}
                            </flux:badge>
                        </div>
                    </flux:card>
                </button>
            @endforeach
        </div>
    @else
        <flux:card size="sm" class="mb-6 flex items-center gap-3">
            <flux:icon.check-circle class="text-on-surface size-5 shrink-0" />
            <flux:text>Nenhuma revisão pendente para hoje.</flux:text>
        </flux:card>
    @endif

    <flux:modal
        wire:model="showReviewModal"
        name="revisao-anotacao-{{ $referenceMaterialId }}"
        class="w-full max-w-[calc(100vw-2rem)] p-0 sm:max-w-2xl"
    >
        @if ($this->readingNoteUnderReview)
            @php($note = $this->readingNoteUnderReview)

            <div class="flex max-h-[85dvh] flex-col" wire:key="review-reading-note-{{ $note->id }}">
                <div class="border-outline-variant/50 shrink-0 border-b px-6 pt-6 pb-4">
                    <flux:heading size="lg" class="pe-10">{{ $note->displayTitle() }}</flux:heading>

                    @if ($note->location)
                        <flux:text size="sm" class="text-on-surface-variant mt-1">
                            {{ $note->location }}
                        </flux:text>
                    @endif
                </div>

                <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-6 py-5">
                    @if ($note->tags)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($note->tags as $tag)
                                <flux:badge size="sm" variant="pill" wire:key="review-tag-{{ $loop->index }}">
                                    {{ $tag }}
                                </flux:badge>
                            @endforeach
                        </div>
                    @endif

                    <section class="space-y-2">
                        <flux:heading size="sm" class="text-on-surface-variant uppercase">Anotação</flux:heading>
                        <div class="prose prose-sm dark:prose-invert max-w-none">{!! Str::markdownRich($note->body, ['html_input' => 'strip']) !!}</div>
                    </section>
                </div>

                <div class="border-outline-variant/50 bg-surface-container-low shrink-0 border-t px-6 py-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button type="button" variant="ghost" wire:click="closeReview">
                            Parar por aqui
                        </flux:button>

                        <flux:spacer />

                        <flux:button type="button" variant="danger" icon="arrow-path" wire:click="grade(false)">
                            Não lembro
                        </flux:button>

                        <flux:button type="button" variant="primary" icon="check" wire:click="grade(true)">
                            Lembrei
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
