<div class="mx-auto w-full max-w-7xl py-8">
    @php($project = $this->project)

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <flux:heading size="xl" level="1">{{ $project->name }}</flux:heading>
            @if ($project->description)
                <flux:text class="mt-2 max-w-2xl">{{ $project->description }}</flux:text>
            @endif
            @if ($project->repository_url)
                <flux:link href="{{ $project->repository_url }}" target="_blank" class="mt-1 inline-block text-sm">{{ $project->repository_url }}</flux:link>
            @endif
        </div>

        <div class="flex gap-2">
            <flux:modal.trigger name="edit-project"><flux:button variant="ghost" icon="pencil-square">Editar</flux:button></flux:modal.trigger>
            <flux:modal.trigger name="delete-project"><flux:button variant="ghost" icon="trash" /></flux:modal.trigger>
        </div>
    </div>

    @php($githubRepository = $project->githubRepository())

    <div @class(['grid grid-cols-1 gap-6', 'lg:grid-cols-2' => $githubRepository])>
    <x-island title="Documentação" icon="document-text" height="h-[34rem]">
        <x-slot:actions>
            <flux:badge size="sm" color="zinc">{{ $project->docs->count() }}</flux:badge>
            <flux:modal.trigger name="add-doc"><flux:button size="sm" icon="plus">Novo documento</flux:button></flux:modal.trigger>
        </x-slot:actions>

        <flux:input icon="magnifying-glass" wire:model.live.debounce.250ms="docFilter" placeholder="Filtrar documentos por título ou categoria..." clearable class="mb-5" />

        <div class="space-y-6">
            @forelse ($this->docsByCategory as $category => $docs)
                <section wire:key="category-{{ $category }}">
                    <flux:heading size="sm" class="text-on-surface-variant mb-2">{{ $category }} <span class="font-normal">({{ $docs->count() }})</span></flux:heading>
                    <div @class(['grid grid-cols-1 gap-3 sm:grid-cols-2', 'xl:grid-cols-3' => ! $githubRepository])>
                        @foreach ($docs as $doc)
                            <a wire:key="doc-{{ $doc->id }}" href="{{ route('projetos.docs.show', ['project' => $project->id, 'doc' => $doc->id]) }}" wire:navigate class="border-surface-variant bg-surface-container-low hover:bg-surface-variant/40 focus-visible:outline-primary flex flex-col justify-between rounded-xl border p-4 transition-colors focus-visible:outline-2">
                                <flux:text class="line-clamp-2 font-medium">{{ $doc->title }}</flux:text>
                                <flux:text size="xs" class="text-on-surface-variant mt-3">Atualizado em {{ $doc->updated_at->format('d/m/Y') }}</flux:text>
                            </a>
                        @endforeach
                    </div>
                </section>
            @empty
                @if (filled($docFilter))
                    <flux:text size="sm" class="text-on-surface-variant">Nenhum documento corresponde a "{{ $docFilter }}".</flux:text>
                @else
                    <x-empty-state icon="document-text" heading="Sem documentação" description="Crie o primeiro documento em Markdown deste projeto." />
                @endif
            @endforelse
        </div>
    </x-island>

    @if ($githubRepository)
        @php($commits = $this->commits)
        <x-island title="Commits" icon="code-bracket" height="h-[34rem]">
            <x-slot:actions>
                <flux:badge size="sm" color="zinc" class="font-mono">{{ $githubRepository }}</flux:badge>
            </x-slot:actions>

            @if ($commits['error'])
                <flux:callout icon="exclamation-triangle" variant="warning" :heading="$commits['error']" class="mb-4" />
            @endif

            <div class="divide-surface-variant divide-y">
                @forelse ($commits['commits'] as $commit)
                    <div wire:key="commit-{{ $commit['sha'] }}" class="flex items-start gap-3 py-2.5">
                        <flux:link href="{{ $commit['url'] }}" target="_blank" class="shrink-0 font-mono text-xs">{{ Str::substr($commit['sha'], 0, 7) }}</flux:link>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm">{{ $commit['message'] }}</span>
                            @foreach ($this->linkedCommits->get($commit['sha'], []) as $link)
                                <a wire:key="commit-{{ $commit['sha'] }}-link-{{ $link->id }}" href="{{ route('trabalho.show', $link->work_item_id) }}" wire:navigate class="mt-1 inline-block">
                                    <flux:badge size="sm" :color="$link->workItem->kind->badgeColor()" icon="link">{{ Str::limit($link->workItem->title, 40) }}</flux:badge>
                                </a>
                            @endforeach
                            <span class="text-on-surface-variant block text-xs">
                                {{ $commit['author'] }}@if ($commit['committed_at']) · {{ \Illuminate\Support\Carbon::parse($commit['committed_at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}@endif
                            </span>
                        </span>
                    </div>
                @empty
                    @unless ($commits['error'])
                        <x-empty-state icon="code-bracket" heading="Sem commits" description="Nenhum commit encontrado neste repositório." />
                    @endunless
                @endforelse
            </div>

            @if ($commits['hasMore'])
                <flux:button size="sm" variant="ghost" wire:click="loadMoreCommits" class="mt-3 w-full">Carregar mais</flux:button>
            @endif
        </x-island>
    @endif
    </div>

    <div class="mt-10 mb-4 flex items-center justify-between">
        <flux:heading size="lg">Demandas</flux:heading>
        <flux:modal.trigger name="add-work-item"><flux:button size="sm" icon="plus">Nova demanda</flux:button></flux:modal.trigger>
    </div>
    @php($isDeliveredTab = $workItemTab === 'entregues')
    <flux:radio.group wire:model.live="workItemTab" variant="segmented" class="mb-3">
        <flux:radio value="ativas">
            Ativas
            <flux:badge size="sm">{{ $this->activeWorkItems->count() }}</flux:badge>
        </flux:radio>
        <flux:radio value="entregues">
            Entregues
            <flux:badge size="sm">{{ $this->deliveredWorkItems->count() }}</flux:badge>
        </flux:radio>
    </flux:radio.group>

    <div class="divide-surface-variant border-surface-variant divide-y rounded-xl border">
        @forelse (($isDeliveredTab ? $this->deliveredWorkItems : $this->activeWorkItems) as $item)
            <div wire:key="item-{{ $item->id }}" class="hover:bg-surface-variant/40 flex items-center gap-2 pr-2 transition-colors">
                <a href="{{ route('trabalho.show', $item->id) }}" wire:navigate class="flex min-w-0 flex-1 items-center justify-between gap-3 py-3 pl-4">
                    <span class="flex min-w-0 items-center gap-2">
                        <flux:badge size="sm" :color="$item->kind->badgeColor()" class="font-mono">{{ $item->kind->value }}</flux:badge>
                        <span class="min-w-0">
                            <span class="block truncate">{{ $item->title }}</span>
                            @if (filled($item->description))
                                <span class="text-on-surface-variant mt-0.5 line-clamp-1 block text-xs">{{ Str::of(strip_tags(Str::markdown($item->description, ['html_input' => 'strip'])))->squish() }}</span>
                            @endif
                        </span>
                    </span>
                    <flux:badge size="sm" :color="$item->status->badgeColor()">{{ $item->status->label() }}</flux:badge>
                </a>
                <flux:button size="xs" variant="ghost" icon="trash" wire:click="confirmDeleteWorkItem({{ $item->id }})" aria-label="Excluir demanda" />
            </div>
        @empty
            <flux:text class="p-4">{{ $isDeliveredTab ? 'Nenhuma demanda entregue ainda.' : 'Nenhuma demanda ativa neste projeto.' }}</flux:text>
        @endforelse
    </div>

    <flux:modal name="add-doc" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-md">
        <form wire:submit="createDoc" class="space-y-5">
            <flux:heading size="lg">Novo documento</flux:heading>
            <flux:input label="Título" wire:model="docTitle" />
            <flux:input label="Categoria" wire:model="docCategory" placeholder="Ex: Módulo de Autenticação" list="doc-categories" />
            <datalist id="doc-categories">
                @foreach ($this->docsByCategory->keys() as $category)
                    <option value="{{ $category }}"></option>
                @endforeach
            </datalist>
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Criar e editar</flux:button></div>
        </form>
    </flux:modal>

    <flux:modal name="add-work-item" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-xl">
        <form wire:submit="createWorkItem" class="space-y-5">
            <div>
                <flux:heading size="lg">Nova demanda</flux:heading>
                <flux:text class="mt-2">Vinculada a {{ $project->name }}.</flux:text>
            </div>
            <flux:input label="Título" wire:model="workItemTitle" />
            <x-work-item-kind-picker model="workItemKind" :value="$workItemKind" />
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Criar demanda</flux:button></div>
        </form>
    </flux:modal>

    <flux:modal name="edit-project" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-lg">
        <form wire:submit="updateProject" class="space-y-5">
            <flux:heading size="lg">Editar projeto</flux:heading>
            <flux:input label="Nome" wire:model="name" />
            <flux:input label="Repositório (URL)" wire:model="repository_url" />
            <flux:textarea label="Visão geral" wire:model="description" rows="3" />
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Salvar</flux:button></div>
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
                <flux:button variant="danger" wire:click="deleteWorkItem">Excluir</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-project" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Excluir projeto?</flux:heading>
                <flux:text class="mt-2">Os documentos serão excluídos. As demandas ficam sem projeto.</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="deleteProject">Excluir</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
