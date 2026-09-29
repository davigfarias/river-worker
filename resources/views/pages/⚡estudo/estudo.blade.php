<div class="mx-auto w-full max-w-7xl py-8">
    @php($study = $this->study)

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ route('estudos') }}" wire:navigate>Estudos</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $study->topic ?? 'Estudo' }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl" level="1" class="mt-2">{{ $study->title }}</flux:heading>
        </div>
        <flux:modal.trigger name="delete-study"><flux:button variant="ghost" icon="trash" /></flux:modal.trigger>
    </div>

    {{-- Resumo central: descreve o código e a implementação (Markdown + Mermaid). --}}
    <section class="border-surface-variant bg-surface-container-low mb-8 rounded-xl border p-5">
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="sm" class="text-on-surface-variant uppercase">Resumo</flux:heading>
            @if ($editingSummary)
                <div class="flex gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="$set('editingSummary', false)">Cancelar</flux:button>
                    <flux:button size="sm" variant="primary" wire:click="saveSummary">Salvar</flux:button>
                </div>
            @else
                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="$set('editingSummary', true)">Editar</flux:button>
            @endif
        </div>

        @if ($editingSummary)
            <div wire:key="summary-edit" class="space-y-3">
                <div class="flex gap-3">
                    <flux:input wire:model="title" class="flex-1" />
                    <flux:input wire:model="topic" placeholder="Tópico" class="w-48" />
                </div>
                <div wire:ignore class="border-surface-variant rounded-xl border">
                    <div x-data="markdownEditor('summary', '200px')"><textarea x-ref="textarea"></textarea></div>
                </div>
            </div>
        @else
            <article class="prose dark:prose-invert max-w-none">{!! Str::markdownRich((string) $study->description, ['html_input' => 'strip']) !!}</article>
        @endif
    </section>

    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">Passos</flux:heading>
        <flux:button size="sm" icon="plus" wire:click="newStep">Novo passo</flux:button>
    </div>

    @if ($stepFormOpen)
        <form wire:key="step-form-{{ $editingStepId ?? 'new' }}" wire:submit="saveStep" class="border-surface-variant mb-8 space-y-4 rounded-xl border p-5">
            <div class="flex gap-3">
                <flux:input label="Título do passo" wire:model="stepTitle" class="flex-1" placeholder="Passo 2: extração de interface" />
                <flux:input label="Linguagem" wire:model="stepLanguage" class="w-40" placeholder="php" />
            </div>
            <div class="grid gap-4 lg:grid-cols-2">
                <flux:textarea label="Código" wire:model="stepSnippet" rows="14" class="font-mono" />
                <div>
                    <flux:label>Anotações (Markdown)</flux:label>
                    <div wire:ignore class="border-surface-variant mt-2 rounded-xl border">
                        <div x-data="markdownEditor('stepMarkdown', '260px')"><textarea x-ref="textarea"></textarea></div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('stepFormOpen', false)">Cancelar</flux:button>
                <flux:button type="submit" variant="primary">Salvar passo</flux:button>
            </div>
        </form>
    @endif

    <div wire:sort="sortStep" class="space-y-6">
        @forelse ($study->steps as $step)
            <section wire:key="step-{{ $step->id }}" wire:sort:item="{{ $step->id }}" class="border-surface-variant bg-surface-container-lowest rounded-xl border">
                <header class="border-surface-variant flex items-center gap-3 border-b px-4 py-3">
                    <flux:icon name="bars-3" wire:sort:handle class="text-on-surface-variant size-5 cursor-grab" />
                    <flux:heading class="flex-1">{{ $step->title }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $step->language }}</flux:badge>
                    <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="editStep({{ $step->id }})" />
                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeStep({{ $step->id }})" wire:confirm="Excluir este passo?" />
                </header>

                <div class="grid divide-y lg:grid-cols-2 lg:divide-x lg:divide-y-0 divide-surface-variant">
                    <div class="min-w-0 overflow-x-auto p-4">
                        <pre class="text-sm"><code class="language-{{ $step->language }}">{{ $step->snippet }}</code></pre>
                    </div>
                    <div class="prose dark:prose-invert max-w-none p-4">{!! Str::markdownRich((string) $step->markdown, ['html_input' => 'strip']) !!}</div>
                </div>
            </section>
        @empty
            <x-empty-state icon="code-bracket-square" heading="Sem passos" description="Adicione o código gerado inicial e evolua passo a passo." />
        @endforelse
    </div>

    <flux:modal name="delete-study" class="min-w-88">
        <div class="space-y-6">
            <flux:heading size="lg">Excluir estudo?</flux:heading>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Excluir</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
