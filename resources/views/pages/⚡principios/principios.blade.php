<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Princípios</flux:heading>
            <flux:text class="mt-2">SOLID, padrões, arquitetura: o catálogo para balizar decisões.</flux:text>
        </div>
        <flux:button icon="plus" wire:click="create">Novo princípio</flux:button>
    </div>

    <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="filter" placeholder="Filtrar por nome, sigla, categoria ou resumo..." clearable class="mb-6 max-w-md" />

    <div class="space-y-3">
        @forelse ($this->principles as $principle)
            <details wire:key="principle-{{ $principle->id }}" class="border-surface-variant bg-surface-container-lowest group rounded-xl border">
                <summary class="flex cursor-pointer items-center gap-3 px-5 py-4 select-none">
                    @if ($principle->acronym)
                        <flux:badge color="zinc">{{ $principle->acronym }}</flux:badge>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading>{{ $principle->name }}</flux:heading>
                            @if ($principle->category)
                                <flux:badge size="sm" color="purple">{{ $principle->category }}</flux:badge>
                            @endif
                        </div>
                        <flux:text size="sm" class="truncate">{{ $principle->summary }}</flux:text>
                    </div>
                </summary>

                <div class="border-surface-variant border-t px-5 py-4">
                    <article class="prose dark:prose-invert max-w-none">{!! Str::markdownRich((string) $principle->description, ['html_input' => 'strip']) !!}</article>
                    <div class="mt-4 flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $principle->id }})">Editar</flux:button>
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $principle->id }})" wire:confirm="Excluir este princípio?" />
                    </div>
                </div>
            </details>
        @empty
            <x-empty-state icon="scale" heading="Nenhum princípio" description="Cadastre o primeiro princípio do catálogo." />
        @endforelse
    </div>

    <flux:modal name="principle-form" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-2xl">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">{{ $editingId ? 'Editar princípio' : 'Novo princípio' }}</flux:heading>
            <div class="flex gap-3">
                <flux:input label="Nome" wire:model="name" class="flex-1" placeholder="Single Responsibility" />
                <flux:input label="Sigla" wire:model="acronym" class="w-28" placeholder="SRP" />
            </div>
            <flux:input label="Categoria" wire:model="category" placeholder="Ex: SOLID, Arquitetura, Boas práticas" list="principle-categories" />
            <datalist id="principle-categories">
                @foreach ($this->categories as $category)
                    <option value="{{ $category }}"></option>
                @endforeach
            </datalist>
            <flux:input label="Resumo" wire:model="summary" />
            <div>
                <flux:label>Descrição</flux:label>
                <div wire:ignore class="border-surface-variant mt-2 rounded-xl border">
                    <div x-data="markdownEditor('description', '220px')"><textarea x-ref="textarea"></textarea></div>
                </div>
            </div>
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Salvar</flux:button></div>
        </form>
    </flux:modal>
</div>
