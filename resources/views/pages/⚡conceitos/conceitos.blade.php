<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Conceitos</flux:heading>
            <flux:text class="mt-2">Glossário técnico com exemplos curtos e vínculo a princípios.</flux:text>
        </div>
        <flux:button icon="plus" wire:click="create">Novo conceito</flux:button>
    </div>

    <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="filter" placeholder="Filtrar termos..." clearable class="mb-6 max-w-md" />

    <div class="space-y-3">
        @forelse ($this->concepts as $concept)
            <div wire:key="concept-{{ $concept->id }}" class="border-surface-variant bg-surface-container-lowest rounded-xl border p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading>{{ $concept->title }}</flux:heading>
                        @if ($concept->principle)
                            <flux:badge size="sm" color="zinc" class="mt-1">{{ $concept->principle->acronym ?? $concept->principle->name }}</flux:badge>
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="edit({{ $concept->id }})" />
                        <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $concept->id }})" wire:confirm="Excluir este conceito?" />
                    </div>
                </div>
                <flux:text class="mt-2">{{ $concept->definition }}</flux:text>
                @if ($concept->code_snippet)
                    <pre class="mt-3 overflow-x-auto rounded-lg text-sm"><code class="language-php">{{ $concept->code_snippet }}</code></pre>
                @endif
            </div>
        @empty
            <x-empty-state icon="light-bulb" heading="Nenhum conceito" description="Cadastre o primeiro termo do glossário." />
        @endforelse
    </div>

    <flux:modal name="concept-form" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-xl">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">{{ $editingId ? 'Editar conceito' : 'Novo conceito' }}</flux:heading>
            <flux:input label="Termo" wire:model="title" />
            <flux:textarea label="Definição" wire:model="definition" rows="3" />
            <flux:textarea label="Exemplo de código" wire:model="code_snippet" rows="5" class="font-mono" />
            <flux:select label="Princípio relacionado" wire:model="principle_id" placeholder="Nenhum">
                @foreach ($this->principles as $principle)
                    <flux:select.option value="{{ $principle->id }}">{{ $principle->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Salvar</flux:button></div>
        </form>
    </flux:modal>
</div>
