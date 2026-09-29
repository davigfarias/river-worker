<div class="mx-auto w-full max-w-7xl py-8">
    @php($doc = $this->doc)

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('projetos') }}" wire:navigate>Projetos</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('projetos.show', $doc->project->slug) }}" wire:navigate>{{ $doc->project->name }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $doc->category }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex gap-2">
            @if ($editing)
                <flux:button variant="ghost" wire:click="$set('editing', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">Salvar</flux:button>
            @else
                <flux:button variant="ghost" icon="pencil-square" wire:click="$set('editing', true)">Editar</flux:button>
                <flux:modal.trigger name="delete-doc"><flux:button variant="ghost" icon="trash" /></flux:modal.trigger>
            @endif
        </div>
    </div>

    @if ($editing)
        <div class="space-y-4" wire:key="doc-edit">
            <flux:input label="Título" wire:model="title" />
            <flux:input label="Categoria" wire:model="category" />
            <div wire:ignore class="border-surface-variant rounded-xl border">
                <div x-data="markdownEditor('content', '400px')"><textarea x-ref="textarea"></textarea></div>
            </div>
        </div>
    @else
        <flux:heading size="xl" level="1">{{ $doc->title }}</flux:heading>
        <article class="prose dark:prose-invert mt-6 max-w-none">{!! Str::markdownRich((string) $doc->content, ['html_input' => 'strip']) !!}</article>
    @endif

    <flux:modal name="delete-doc" class="min-w-88">
        <div class="space-y-6">
            <flux:heading size="lg">Excluir documento?</flux:heading>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete">Excluir</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
