<div class="mx-auto w-full max-w-7xl space-y-6 py-8">
    @php($data = $this->dashboard)
    @php($focus = $data['focus'] ?? null)
    @php($totalOpen = collect($data['pipeline'] ?? [])->except('deployed')->sum())

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Foco</flux:heading>
            <flux:text class="mt-1">
                @if ($data['project'] ?? null)
                    Projeto ativo: {{ $data['project']->name }} &middot; {{ $totalOpen }} {{ $totalOpen === 1 ? 'demanda aberta' : 'demandas abertas' }}
                @else
                    Cadastre uma demanda para começar.
                @endif
            </flux:text>
        </div>

        <flux:modal.trigger name="add-work-item">
            <flux:button icon="plus" variant="primary">Nova demanda</flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Pipeline: onde está todo o trabalho, do backlog à produção. --}}
    <div class="border-outline-variant/40 bg-surface-container-lowest grid grid-cols-2 overflow-hidden rounded-2xl border shadow-sm sm:grid-cols-3 lg:grid-cols-7" role="list" aria-label="Demandas por etapa">
        @foreach (\App\Enums\WorkItemStatus::cases() as $status)
            @php($count = $data['pipeline'][$status->value] ?? 0)
            <div wire:key="stage-{{ $status->value }}" role="listitem" class="border-outline-variant/30 relative border-s px-3 pt-4 pb-3 first:border-s-0 max-lg:[&:nth-child(n+3)]:border-t {{ $count === 0 ? 'opacity-60' : '' }}">
                <span class="{{ $status->barClass() }} absolute inset-x-0 top-0 h-1"></span>
                <div class="text-2xl font-semibold tabular-nums">{{ $count }}</div>
                <flux:text class="mt-1 text-xs leading-tight">{{ $status->label() }}</flux:text>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Foco ativo --}}
        <x-island title="Foco ativo" icon="bolt" height="h-80" class="lg:col-span-2">
            @if ($focus)
                <div class="flex h-full flex-col">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:badge :color="$focus->status->badgeColor()">{{ $focus->status->label() }}</flux:badge>
                        @if ($data['project'])
                            <flux:text size="sm" class="text-on-surface-variant">{{ $data['project']->name }}</flux:text>
                        @endif
                    </div>

                    <a href="{{ route('trabalho.show', $focus->id) }}" wire:navigate class="hover:text-primary mt-3 block">
                        <flux:heading size="xl" class="leading-snug">{{ $focus->title }}</flux:heading>
                    </a>

                    <div class="mt-auto space-y-4">
                        <div>
                            <div class="bg-surface-variant h-2 rounded-full">
                                <div class="bg-primary h-2 rounded-full transition-[width]" style="width: {{ $data['progress'] }}%"></div>
                            </div>
                            <flux:text size="sm" class="text-on-surface-variant mt-2">
                                Checklist {{ $data['doneSteps'] }}/{{ $data['totalSteps'] }}
                                &middot; {{ $data['pendingFiles'] }} {{ $data['pendingFiles'] === 1 ? 'arquivo pendente' : 'arquivos pendentes' }} de revisão
                            </flux:text>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <flux:text>
                                @if ($data['nextStep'])
                                    Próximo passo: <strong>{{ $data['nextStep']->title }}</strong>
                                @else
                                    Checklist completo.
                                @endif
                            </flux:text>
                            <flux:button size="sm" icon-trailing="arrow-right" href="{{ route('trabalho.show', $focus->id) }}" wire:navigate>Abrir demanda</flux:button>
                        </div>
                    </div>
                </div>
            @else
                <flux:text>Nada em andamento{{ $data['project'] ? ' em '.$data['project']->name : '' }}. Mova uma demanda do backlog para começar.</flux:text>
            @endif
        </x-island>

        {{-- Semana --}}
        <x-island title="Esta semana" icon="calendar-days" height="h-80">
            <dl class="divide-outline-variant/30 -my-3 divide-y">
                @foreach ([
                    ['Deploys nos últimos 7 dias', $data['weekDeploys'] ?? 0],
                    ['Revisões de leitura para hoje', $data['dueReviews'] ?? 0],
                    ['Arquivos pendentes no foco', $data['pendingFiles'] ?? 0],
                ] as [$label, $value])
                    <div class="flex items-center justify-between gap-4 py-4">
                        <dt><flux:text>{{ $label }}</flux:text></dt>
                        <dd class="text-2xl font-semibold tabular-nums">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-island>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Fila --}}
        <x-island title="Fila de trabalho" icon="queue-list" height="h-96">
            @php($queue = collect($data['inDev'] ?? [])->concat($data['backlog'] ?? []))
            <div class="space-y-2">
                @forelse ($queue as $item)
                    <a wire:key="queue-{{ $item->id }}" href="{{ route('trabalho.show', $item->id) }}" wire:navigate class="border-surface-variant hover:bg-surface-variant/40 flex items-center gap-3 rounded-lg border p-3 transition-colors">
                        <span class="{{ $item->status->barClass() }} h-8 w-1 shrink-0 rounded-full"></span>
                        <div class="min-w-0 flex-1">
                            <flux:text class="truncate font-medium">{{ $item->title }}</flux:text>
                            <flux:text size="xs" class="text-on-surface-variant">{{ $item->status->label() }}</flux:text>
                        </div>
                        <flux:badge size="sm" :color="$item->kind->badgeColor()" class="font-mono">{{ $item->kind->value }}</flux:badge>
                    </a>
                @empty
                    <flux:text size="sm" class="text-on-surface-variant">Fila vazia.</flux:text>
                @endforelse
            </div>
        </x-island>

        {{-- Lendo agora --}}
        <x-island title="Lendo agora" icon="book-open" height="h-96">
            <x-slot:actions>
                <flux:button size="xs" variant="ghost" href="{{ route('referencias') }}" wire:navigate>Ver recursos</flux:button>
            </x-slot:actions>

            <div class="space-y-4">
                @forelse ($data['reading'] ?? [] as $material)
                    <a wire:key="reading-{{ $material->id }}" href="{{ route('referencias.show', $material->id) }}" wire:navigate class="block">
                        <flux:text class="truncate font-medium">{{ $material->title }}</flux:text>
                        @if ($material->hasReadingProgress())
                            <div class="bg-surface-variant mt-2 h-1.5 rounded-full">
                                <div class="bg-primary h-1.5 rounded-full" style="width: {{ $material->readingProgressPercent() }}%"></div>
                            </div>
                            <flux:text size="xs" class="text-on-surface-variant mt-1">{{ $material->pagesRead() }}/{{ $material->pagesTotal() }} págs</flux:text>
                        @else
                            <flux:text size="xs" class="text-on-surface-variant">{{ $material->typeIcon()->label() }}</flux:text>
                        @endif
                    </a>
                @empty
                    <flux:text size="sm" class="text-on-surface-variant">Nada em leitura no momento.</flux:text>
                @endforelse
            </div>
        </x-island>

        {{-- Docs recentes --}}
        <x-island title="Documentação recente" icon="document-text" height="h-96">
            <div class="space-y-2">
                @forelse ($data['recentDocs'] ?? [] as $doc)
                    <a wire:key="doc-{{ $doc->id }}" href="{{ route('projetos.docs.show', ['project' => $doc->project_id, 'doc' => $doc->id]) }}" wire:navigate class="hover:bg-surface-variant/40 block rounded-lg p-3 transition-colors">
                        <flux:text class="truncate font-medium">{{ $doc->title }}</flux:text>
                        <flux:text size="xs" class="text-on-surface-variant">{{ $doc->project->name }} &middot; {{ $doc->category }}</flux:text>
                    </a>
                @empty
                    <flux:text size="sm" class="text-on-surface-variant">Nenhum documento ainda.</flux:text>
                @endforelse
            </div>
        </x-island>
    </div>

    {{-- Atalhos --}}
    <nav aria-label="Atalhos" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-7">
        @foreach ($this->shortcuts as $shortcut)
            <a wire:key="shortcut-{{ $shortcut['route'] }}" href="{{ route($shortcut['route']) }}" wire:navigate class="border-outline-variant/40 bg-surface-container-lowest hover:bg-surface-variant/40 focus-visible:outline-primary flex items-center gap-3 rounded-xl border p-3 transition-colors focus-visible:outline-2">
                <flux:icon :name="$shortcut['icon']" class="text-on-surface-variant size-5 shrink-0" />
                <div class="min-w-0">
                    <flux:text class="truncate font-medium">{{ $shortcut['label'] }}</flux:text>
                    <flux:text size="xs" class="text-on-surface-variant">
                        {{ $shortcut['count'] === null ? $shortcut['unit'] : $shortcut['count'].' '.($shortcut['count'] === 1 ? $shortcut['unit'] : $shortcut['plural']) }}
                    </flux:text>
                </div>
            </a>
        @endforeach
    </nav>

    <flux:modal name="add-work-item" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-xl">
        <form wire:submit="createWorkItem" class="space-y-5">
            <flux:heading size="lg">Nova demanda</flux:heading>
            <flux:input label="Título" wire:model="title" />
            <x-work-item-kind-picker model="kind" :value="$kind" />
            <flux:select label="Projeto" wire:model="project_id" placeholder="Sem projeto">
                @foreach ($this->projects as $project)
                    <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:textarea label="Especificação inicial (Markdown)" wire:model="description" rows="4" />
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Criar demanda</flux:button></div>
        </form>
    </flux:modal>
</div>
