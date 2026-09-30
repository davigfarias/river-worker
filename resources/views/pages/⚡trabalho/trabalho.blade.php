<div class="mx-auto w-full max-w-7xl py-8">
    @php($item = $this->item)
    @php($doneSteps = $item->steps->where('is_completed', true)->count())
    @php($statuses = \App\Enums\WorkItemStatus::cases())
    @php($currentIndex = array_search($item->status, $statuses, true))

    <flux:breadcrumbs class="mb-4">
        <flux:breadcrumbs.item href="{{ route('projetos') }}" wire:navigate>Projetos</flux:breadcrumbs.item>
        @if ($item->project)
            <flux:breadcrumbs.item href="{{ route('projetos.show', $item->project->slug) }}" wire:navigate>{{ $item->project->name }}</flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>Sem projeto</flux:breadcrumbs.item>
        @endif
        <flux:breadcrumbs.item>Demanda</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="min-w-0">
            @if ($editingTitle)
                <form wire:submit="saveTitle" wire:key="title-edit" x-on:keydown.escape="$wire.cancelTitleEdit()" class="max-w-xl space-y-3">
                    <div class="flex items-start gap-2">
                        <flux:input wire:model="title" autofocus aria-label="Título da demanda" class="min-w-72" />
                        <flux:button type="submit" variant="primary" icon="check" aria-label="Salvar" />
                        <flux:button variant="ghost" icon="x-mark" wire:click="cancelTitleEdit" aria-label="Cancelar" />
                    </div>
                    <x-work-item-kind-picker model="kind" :value="$kind" />
                </form>
            @else
                <div class="group flex items-center gap-2" wire:key="title-view">
                    <flux:badge :color="$item->kind->badgeColor()" class="font-mono" title="{{ $item->kind->label() }}">{{ $item->kind->value }}</flux:badge>
                    <flux:heading size="xl" level="1" class="cursor-text" title="Duplo clique para editar" wire:dblclick="$set('editingTitle', true)">{{ $item->title }}</flux:heading>
                    <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="$set('editingTitle', true)" aria-label="Editar título e natureza" />
                </div>
            @endif
        </div>

        <flux:modal.trigger name="delete-work-item">
            <flux:button variant="ghost" icon="trash" aria-label="Excluir demanda" />
        </flux:modal.trigger>
    </div>

    {{-- Prioridade (opcional, manual) --}}
    @php($priority = $this->priority)
    <div class="border-outline-variant/40 bg-surface-container-lowest mb-5 flex flex-wrap items-center gap-3 rounded-2xl border px-5 py-3 shadow-sm" wire:key="priority-strip">
        <flux:icon name="flag" class="text-on-surface-variant size-5 shrink-0" />
        <flux:heading>Prioridade</flux:heading>

        @if ($priority)
            <flux:badge :color="$priority->priority->badgeColor()">{{ $priority->priority->label() }}</flux:badge>
            <flux:text class="min-w-0 flex-1">
                {{ $priority->explanation }}
                <span class="text-on-surface-variant">· {{ $priority->escalated ? 'escalada por regra' : 'score '.number_format($priority->score, 1, ',') }}</span>
            </flux:text>
            <flux:button size="sm" variant="ghost" icon="trash" wire:click="clearAssessment" aria-label="Limpar avaliação" />
            <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="startAssessment">Reavaliar</flux:button>
        @else
            <flux:text class="text-on-surface-variant flex-1">Opcional. Responda algumas perguntas para medir a prioridade.</flux:text>
            <flux:button size="sm" variant="primary" icon="flag" wire:click="startAssessment">Avaliar prioridade</flux:button>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Especificação --}}
        <x-island title="Especificação" icon="document-text" wire:key="island-spec">
            <x-slot:actions>
                @if ($editingSpec)
                    <flux:button size="sm" variant="ghost" wire:click="$set('editingSpec', false)">Cancelar</flux:button>
                    <flux:button size="sm" variant="primary" wire:click="saveSpec">Salvar</flux:button>
                @else
                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="$set('editingSpec', true)">Editar</flux:button>
                @endif
            </x-slot:actions>

            @if ($editingSpec)
                <div
                    class="spec-editor"
                    wire:key="spec-edit"
                    x-on:keydown.escape="$wire.set('editingSpec', false)"
                    x-on:keydown.meta.enter.prevent="$wire.saveSpec()"
                    x-on:keydown.ctrl.enter.prevent="$wire.saveSpec()"
                >
                    <div wire:ignore>
                        <div x-data="markdownEditor('description', '220px')"><textarea x-ref="textarea"></textarea></div>
                    </div>
                </div>
            @elseif (filled($item->description))
                <article class="prose prose-lg dark:prose-invert max-w-none cursor-text" title="Duplo clique para editar" wire:dblclick="$set('editingSpec', true)">{!! Str::markdownRich($item->description, ['html_input' => 'strip']) !!}</article>
            @else
                <x-empty-state icon="document-text" heading="Sem especificação" description="Descreva o escopo e os prompts base desta demanda." />
            @endif
        </x-island>

        {{-- Ciclo de vida --}}
        <x-island title="Ciclo de vida" icon="arrow-path" wire:key="island-status">
            <x-timeline align="start">
                @foreach ($statuses as $index => $status)
                    @php($isCurrent = $status === $item->status)
                    @php($isReached = $index <= $currentIndex)
                    @php($canClick = $status !== \App\Enums\WorkItemStatus::Deployed && ! $isCurrent)

                    <x-timeline.item wire:key="status-{{ $status->value }}">
                        <x-timeline.indicator :color="$isReached ? $status->badgeColor() : null">
                            @if ($status === \App\Enums\WorkItemStatus::Deployed || ($isReached && ! $isCurrent))
                                <flux:icon :name="$status === \App\Enums\WorkItemStatus::Deployed ? 'rocket-launch' : 'check'" variant="micro" />
                            @else
                                {{ $index + 1 }}
                            @endif
                        </x-timeline.indicator>

                        <x-timeline.content>
                            @if ($status === \App\Enums\WorkItemStatus::Deployed)
                                <div class="-mx-2 flex items-center gap-2 px-2 py-1">
                                    <div class="min-w-0 flex-1">
                                        <flux:heading class="{{ $isCurrent ? '' : 'text-on-surface-variant' }}">{{ $status->label() }}</flux:heading>
                                        @if ($item->deployed_at)
                                            <flux:text size="sm" class="text-on-surface-variant">
                                                {{ $item->deployed_at->format('d/m/Y H:i') }}{{ $item->deploy_version ? ' · '.$item->deploy_version : '' }}
                                            </flux:text>
                                        @endif
                                    </div>

                                    @unless ($isCurrent)
                                        <flux:modal.trigger name="confirm-deploy">
                                            <flux:button size="sm" variant="primary" icon="rocket-launch" aria-label="Fazer deploy" />
                                        </flux:modal.trigger>
                                    @endunless
                                </div>
                            @elseif ($canClick)
                                <button
                                    type="button"
                                    wire:click="changeStatus('{{ $status->value }}')"
                                    class="hover:bg-surface-variant/50 focus-visible:outline-primary -mx-2 w-full rounded-lg px-2 py-1 text-start transition-colors focus-visible:outline-2"
                                >
                                    <flux:text class="{{ $isReached ? 'text-on-surface' : '' }}">{{ $status->label() }}</flux:text>
                                </button>
                            @else
                                <div class="-mx-2 px-2 py-1">
                                    <flux:heading class="{{ $isCurrent ? '' : 'text-on-surface-variant' }}">{{ $status->label() }}</flux:heading>
                                </div>
                            @endif
                        </x-timeline.content>
                    </x-timeline.item>
                @endforeach
            </x-timeline>
        </x-island>

        {{-- Checklist --}}
        <x-island title="Checklist" icon="check-circle" wire:key="island-checklist">
            <x-slot:actions>
                <flux:badge size="sm" :color="$doneSteps === $item->steps->count() && $doneSteps > 0 ? 'green' : 'zinc'">{{ $doneSteps }}/{{ $item->steps->count() }}</flux:badge>
            </x-slot:actions>

            <div class="space-y-3">
                @foreach ($item->steps as $step)
                    <flux:checkbox wire:key="step-{{ $step->id }}" :checked="$step->is_completed" wire:click="toggleStep({{ $step->id }})" :label="$step->title" />
                @endforeach
            </div>
        </x-island>

        {{-- Arquivos para revisão manual --}}
        <x-island title="Arquivos" icon="code-bracket" wire:key="island-files">
            <x-slot:actions>
                <flux:badge size="sm" :color="$item->files->where('is_reviewed', false)->isEmpty() ? 'zinc' : 'amber'">
                    {{ $item->files->where('is_reviewed', false)->count() }} pendentes
                </flux:badge>
            </x-slot:actions>

            <form wire:submit="addFile" class="mb-4 space-y-2">
                <flux:input wire:model="filePath" size="sm" placeholder="app/Services/CheckoutService.php" aria-label="Caminho do arquivo" />
                <div class="flex gap-2">
                    <flux:input wire:model="reasonNotes" size="sm" placeholder="O que precisa mudar" aria-label="O que precisa mudar" class="flex-1" />
                    <flux:button type="submit" size="sm" icon="plus">Adicionar</flux:button>
                </div>
            </form>

            <div class="space-y-2">
                @forelse ($item->files as $file)
                    <div wire:key="file-{{ $file->id }}" class="border-surface-variant flex items-start gap-3 rounded-lg border p-3">
                        <flux:checkbox :checked="$file->is_reviewed" wire:click="toggleFile({{ $file->id }})" aria-label="Marcar como revisado" />
                        <div class="min-w-0 flex-1">
                            <code class="text-sm break-all {{ $file->is_reviewed ? 'line-through opacity-60' : '' }}">{{ $file->file_path }}</code>
                            @if ($file->reason_notes)
                                <flux:text size="sm" class="mt-1">{{ $file->reason_notes }}</flux:text>
                            @endif
                        </div>
                        <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeFile({{ $file->id }})" aria-label="Remover arquivo" />
                    </div>
                @empty
                    <flux:text size="sm" class="text-on-surface-variant">Nenhum arquivo. Registre os que você vai lapidar na IDE.</flux:text>
                @endforelse
            </div>
        </x-island>
    </div>

    <flux:modal name="assessment" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-md">
        @php($steps = $this->assessmentSteps)
        @php($current = $steps[$assessmentStep] ?? null)
        @if ($current)
            <div class="space-y-5" wire:key="assessment-step-{{ $assessmentStep }}">
                <div class="space-y-2">
                    <flux:text size="sm" class="text-on-surface-variant">Pergunta {{ $assessmentStep + 1 }} de {{ count($steps) }}</flux:text>
                    <div class="bg-surface-variant h-2 overflow-hidden rounded-full" role="progressbar" aria-valuemin="0" aria-valuemax="{{ count($steps) }}" aria-valuenow="{{ $assessmentStep + 1 }}">
                        <div class="bg-primary h-full rounded-full transition-all" style="width: {{ ($assessmentStep + 1) / count($steps) * 100 }}%"></div>
                    </div>
                </div>

                <flux:heading size="lg">{{ $current['question'] }}</flux:heading>

                <div class="space-y-2">
                    @foreach ($current['options'] as $value => $label)
                        @php($selected = isset($assessment[$current['key']]) && (string) (int) $assessment[$current['key']] === (string) $value)
                        <button
                            type="button"
                            wire:click="answer('{{ $value }}')"
                            class="{{ $selected ? 'border-primary bg-primary/10' : 'border-surface-variant hover:bg-surface-container-low' }} focus-visible:outline-primary w-full rounded-lg border px-4 py-2.5 text-start transition-colors focus-visible:outline-2"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div class="flex">
                    <flux:button variant="ghost" icon="arrow-left" wire:click="previousAssessmentStep" :disabled="$assessmentStep === 0">Voltar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="confirm-deploy" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-md">
        <form wire:submit="registerDeploy" class="space-y-5">
            <div>
                <flux:heading size="lg">Confirmar deploy?</flux:heading>
                <flux:text class="mt-2">A demanda vai para "Em produção" e o horário atual é registrado.</flux:text>
            </div>
            <flux:input label="Versão" wire:model="deployVersion" placeholder="v1.4.0 (opcional)" />
            <flux:textarea label="Notas de lançamento" wire:model="releaseNotes" rows="3" placeholder="Opcional" />
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="rocket-launch">Confirmar deploy</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-work-item" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Excluir demanda?</flux:heading>
                <flux:text class="mt-2">Esta ação não pode ser desfeita.</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Excluir</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
