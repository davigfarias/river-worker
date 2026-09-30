<div class="mx-auto w-full max-w-7xl py-8">
    @php($item = $this->item)
    @php($doneSteps = $item->steps->where('is_completed', true)->count())
    @php($statuses = \App\Enums\WorkItemStatus::cases())
    @php($currentIndex = array_search($item->status, $statuses, true))

    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="min-w-0">
            <flux:heading size="xl" level="1">{{ $item->title }}</flux:heading>
            @if ($item->project)
                <flux:link href="{{ route('projetos.show', $item->project->slug) }}" wire:navigate class="text-sm">{{ $item->project->name }}</flux:link>
            @else
                <flux:badge as="button" size="sm" color="amber" icon="link" wire:click="$set('editingSpec', true)">Sem projeto: vincular</flux:badge>
            @endif
        </div>

        <flux:modal.trigger name="delete-work-item">
            <flux:button variant="ghost" icon="trash" aria-label="Excluir demanda" />
        </flux:modal.trigger>
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
                <div class="space-y-4" wire:key="spec-edit">
                    <flux:input label="Título" wire:model="title" />
                    <flux:select label="Projeto" wire:model="project_id" placeholder="Sem projeto">
                        @foreach ($this->projects as $project)
                            <flux:select.option value="{{ $project->id }}">{{ $project->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:textarea label="Especificação" wire:model="description" rows="8" />
                </div>
            @elseif (filled($item->description))
                <flux:text class="whitespace-pre-wrap">{{ $item->description }}</flux:text>
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
