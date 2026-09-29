<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Projetos</flux:heading>
            <flux:text class="mt-2">Aplicações sob gestão e a documentação de cada uma.</flux:text>
        </div>

        <flux:modal.trigger name="add-project">
            <flux:button icon="plus">Novo projeto</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->projects as $project)
            <a
                href="{{ route('projetos.show', $project->slug) }}"
                wire:navigate
                wire:key="project-{{ $project->id }}"
                class="group border-surface-variant bg-surface-container-lowest flex flex-col justify-between rounded-xl border p-4 shadow-sm transition-shadow hover:shadow-md"
            >
                <div>
                    <flux:heading class="group-hover:text-primary leading-snug">{{ $project->name }}</flux:heading>
                    @if ($project->description)
                        <flux:text size="sm" class="mt-1 line-clamp-2">{{ $project->description }}</flux:text>
                    @endif
                </div>
                <flux:text size="sm" class="text-on-surface-variant mt-4">
                    {{ $project->docs_count }} {{ $project->docs_count === 1 ? 'documento' : 'documentos' }}
                    &middot; {{ $project->work_items_count }} {{ $project->work_items_count === 1 ? 'demanda' : 'demandas' }}
                </flux:text>
            </a>
        @empty
            <x-empty-state icon="folder" heading="Nenhum projeto" description="Cadastre sua primeira aplicação." class="col-span-full" />
        @endforelse
    </div>

    <flux:modal name="add-project" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-lg">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">Novo projeto</flux:heading>

            <flux:input label="Nome" wire:model="name" placeholder="Ex: River Worker" />
            <flux:input label="Repositório (URL)" wire:model="repository_url" placeholder="https:// (opcional)" />
            <flux:textarea label="Visão geral" wire:model="description" rows="3" />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Criar projeto</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
