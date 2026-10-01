@php
    use App\Models\BragEntry;

    $impactIcons = [
        'impact_changed' => 'arrow-path',
        'impact_easier' => 'hand-thumb-up',
        'impact_faster' => 'bolt',
        'impact_clearer' => 'eye',
        'impact_problem_gone' => 'check-badge',
    ];

    // Seções dos painéis: título, ícone e perguntas de resposta longa.
    $sections = [
        ['title' => 'Entrega', 'icon' => 'rocket-launch', 'questions' => collect(BragEntry::QUESTIONS)->only(['contribution', 'stakeholders'])->all(), 'rows' => 3],
        ['title' => 'Impacto na empresa', 'icon' => 'chart-bar', 'questions' => BragEntry::IMPACT_QUESTIONS, 'rows' => 2],
        ['title' => 'Desenvolvimento', 'icon' => 'academic-cap', 'questions' => collect(BragEntry::QUESTIONS)->only(['trainings', 'feedbacks'])->all(), 'rows' => 3],
    ];
@endphp

<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Brag document</flux:heading>
            <flux:text class="mt-2">O que você entregou, para quem e o que mudou depois.</flux:text>
        </div>
        <flux:button icon="plus" wire:click="create">Nova conquista</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->entries as $entry)
            <button type="button" wire:key="brag-{{ $entry->id }}" wire:click="show({{ $entry->id }})"
                class="border-surface-variant bg-surface-container-lowest hover:bg-surface-variant/40 focus-visible:ring-primary flex flex-col gap-4 rounded-xl border p-5 text-left shadow-sm transition-colors focus-visible:ring-2 focus-visible:outline-none">
                <div class="space-y-2">
                    <flux:heading size="lg" class="line-clamp-2">{{ $entry->goal }}</flux:heading>
                    <div class="flex flex-wrap gap-1.5">
                        @if ($entry->project)
                            <flux:badge size="sm" icon="folder" color="purple">{{ $entry->project }}</flux:badge>
                        @endif
                        @if ($entry->deadline)
                            <flux:badge size="sm" icon="calendar" color="zinc">{{ $entry->deadline }}</flux:badge>
                        @endif
                    </div>
                </div>

                @if ($entry->impact_changed)
                    <div class="border-primary/60 border-l-2 pl-3">
                        <flux:text size="sm" class="font-medium">O que mudou</flux:text>
                        <flux:text size="sm" class="line-clamp-3">{{ $entry->impact_changed }}</flux:text>
                    </div>
                @elseif ($entry->contribution)
                    <flux:text size="sm" class="line-clamp-3">{{ $entry->contribution }}</flux:text>
                @endif
            </button>
        @empty
            <x-empty-state class="md:col-span-2 xl:col-span-3" icon="trophy" heading="Nenhuma conquista" description="Registre a primeira entrega para montar seu brag document." />
        @endforelse
    </div>

    {{-- Leitura: pergunta à esquerda, resposta à direita, em ilhas como no resto do app --}}
    <flux:modal name="brag-view" variant="flyout" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-3xl">
        @if ($entry = $this->viewing)
            <div class="space-y-6">
                <div class="pr-8">
                    <flux:heading size="xl">{{ $entry->goal }}</flux:heading>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @if ($entry->project)
                            <flux:badge size="sm" icon="folder" color="purple">{{ $entry->project }}</flux:badge>
                        @endif
                        @if ($entry->deadline)
                            <flux:badge size="sm" icon="calendar" color="zinc">{{ $entry->deadline }}</flux:badge>
                        @endif
                    </div>
                </div>

                @foreach ($sections as $section)
                    @php($answered = collect($section['questions'])->filter(fn ($label, $field) => filled($entry->{$field})))
                    @if ($answered->isNotEmpty())
                        <section class="border-outline-variant/40 bg-surface-container-lowest overflow-hidden rounded-2xl border shadow-sm">
                            <header class="border-outline-variant/30 flex items-center gap-3 border-b px-5 py-3.5">
                                <flux:icon :name="$section['icon']" class="text-on-surface-variant size-5 shrink-0" />
                                <flux:heading class="flex-1 truncate">{{ $section['title'] }}</flux:heading>
                                <flux:badge size="sm" color="zinc">{{ $answered->count() }}/{{ count($section['questions']) }}</flux:badge>
                            </header>

                            <dl class="divide-outline-variant/30 divide-y px-5">
                                @foreach ($answered as $field => $label)
                                    <div class="grid gap-1.5 py-4 sm:grid-cols-[11rem_1fr] sm:gap-6">
                                        <dt class="text-on-surface-variant flex items-start gap-2 text-sm font-medium">
                                            @isset($impactIcons[$field])
                                                <flux:icon :name="$impactIcons[$field]" variant="micro" class="mt-0.5 shrink-0" />
                                            @endisset
                                            {{ $label }}
                                        </dt>
                                        <dd class="text-sm leading-relaxed whitespace-pre-line">{{ $entry->{$field} }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    @endif
                @endforeach

                <div class="flex items-center justify-between">
                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $entry->id }})" wire:confirm="Excluir esta conquista?">Excluir</flux:button>
                    <flux:button size="sm" variant="primary" icon="pencil-square" wire:click="edit({{ $entry->id }})">Editar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Edição: mesmas ilhas, caixa de texto à direita --}}
    <flux:modal name="brag-form" variant="flyout" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-3xl">
        <form wire:submit="save" class="space-y-6">
            <div class="pr-8">
                <flux:heading size="xl">{{ $editingId ? 'Editar conquista' : 'Nova conquista' }}</flux:heading>
                <flux:text class="mt-2">Responda só o que se aplica. O que ficar em branco não aparece no cartão.</flux:text>
            </div>

            <section class="border-outline-variant/40 bg-surface-container-lowest overflow-hidden rounded-2xl border shadow-sm">
                <header class="border-outline-variant/30 flex items-center gap-3 border-b px-5 py-3.5">
                    <flux:icon name="flag" class="text-on-surface-variant size-5 shrink-0" />
                    <flux:heading class="flex-1 truncate">Visão geral</flux:heading>
                </header>
                <div class="space-y-4 p-5">
                    @foreach (['goal' => 'Meta', 'deadline' => 'Prazo', 'project' => 'Projeto'] as $field => $label)
                        <div class="grid gap-1.5 sm:grid-cols-[11rem_1fr] sm:gap-6">
                            <flux:label for="form-{{ $field }}" class="sm:pt-2">{{ $label }}</flux:label>
                            <flux:input id="form-{{ $field }}" wire:model="form.{{ $field }}" />
                        </div>
                    @endforeach
                </div>
            </section>

            @foreach ($sections as $section)
                <section class="border-outline-variant/40 bg-surface-container-lowest overflow-hidden rounded-2xl border shadow-sm">
                    <header class="border-outline-variant/30 flex items-center gap-3 border-b px-5 py-3.5">
                        <flux:icon :name="$section['icon']" class="text-on-surface-variant size-5 shrink-0" />
                        <flux:heading class="flex-1 truncate">{{ $section['title'] }}</flux:heading>
                    </header>
                    <div class="space-y-4 p-5">
                        @foreach ($section['questions'] as $field => $label)
                            <div class="grid gap-1.5 sm:grid-cols-[11rem_1fr] sm:gap-6">
                                <flux:label for="form-{{ $field }}" class="sm:pt-2">{{ $label }}</flux:label>
                                <flux:textarea id="form-{{ $field }}" wire:model="form.{{ $field }}" rows="{{ $section['rows'] }}" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Salvar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
