@php
    use App\Models\PdiItem;

    $icons = ['current_situation' => 'map-pin', 'action' => 'rocket-launch', 'measurement' => 'chart-bar'];
@endphp

<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">PDI</flux:heading>
            <flux:text class="mt-2">Plano de desenvolvimento individual: onde você está, o que vai fazer e como vai medir.</flux:text>
        </div>
        <flux:button icon="plus" wire:click="create">Novo objetivo</flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->items as $item)
            <button type="button" wire:key="pdi-{{ $item->id }}" wire:click="show({{ $item->id }})"
                class="border-surface-variant bg-surface-container-lowest hover:bg-surface-variant/40 focus-visible:ring-primary flex flex-col gap-4 rounded-xl border p-5 text-left shadow-sm transition-colors focus-visible:ring-2 focus-visible:outline-none">
                <div class="space-y-2">
                    <flux:heading size="lg" class="line-clamp-2">{{ $item->objective }}</flux:heading>
                    @if ($item->deadline)
                        <flux:badge size="sm" icon="calendar" color="zinc">{{ $item->deadline }}</flux:badge>
                    @endif
                </div>

                @if ($item->action)
                    <div class="border-primary/60 border-l-2 pl-3">
                        <flux:text size="sm" class="font-medium">Ação</flux:text>
                        <flux:text size="sm" class="line-clamp-3">{{ $item->action }}</flux:text>
                    </div>
                @endif
            </button>
        @empty
            <x-empty-state class="md:col-span-2 xl:col-span-3" icon="academic-cap" heading="Nenhum objetivo" description="Registre o primeiro objetivo para montar seu PDI." />
        @endforelse
    </div>

    <flux:modal name="pdi-view" variant="flyout" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-3xl">
        @if ($item = $this->viewing)
            <div class="space-y-6">
                <div class="pr-8">
                    <flux:heading size="xl">{{ $item->objective }}</flux:heading>
                    @if ($item->deadline)
                        <flux:badge class="mt-3" size="sm" icon="calendar" color="zinc">{{ $item->deadline }}</flux:badge>
                    @endif
                </div>

                <section class="border-outline-variant/40 bg-surface-container-lowest overflow-hidden rounded-2xl border shadow-sm">
                    <dl class="divide-outline-variant/30 divide-y px-5">
                        @foreach (PdiItem::QUESTIONS as $field => $label)
                            @if (filled($item->{$field}))
                                <div class="grid gap-1.5 py-4 sm:grid-cols-[11rem_1fr] sm:gap-6">
                                    <dt class="text-on-surface-variant flex items-start gap-2 text-sm font-medium">
                                        <flux:icon :name="$icons[$field]" variant="micro" class="mt-0.5 shrink-0" />
                                        {{ $label }}
                                    </dt>
                                    <dd class="text-sm leading-relaxed whitespace-pre-line">{{ $item->{$field} }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </section>

                <div class="flex items-center justify-between">
                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="delete({{ $item->id }})" wire:confirm="Excluir este objetivo?">Excluir</flux:button>
                    <flux:button size="sm" variant="primary" icon="pencil-square" wire:click="edit({{ $item->id }})">Editar</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="pdi-form" variant="flyout" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-3xl">
        <form wire:submit="save" class="space-y-6">
            <div class="pr-8">
                <flux:heading size="xl">{{ $editingId ? 'Editar objetivo' : 'Novo objetivo' }}</flux:heading>
                <flux:text class="mt-2">Responda só o que se aplica. O que ficar em branco não aparece no cartão.</flux:text>
            </div>

            <section class="border-outline-variant/40 bg-surface-container-lowest overflow-hidden rounded-2xl border shadow-sm">
                <div class="space-y-4 p-5">
                    <div class="grid gap-1.5 sm:grid-cols-[11rem_1fr] sm:gap-6">
                        <flux:label for="form-objective" class="sm:pt-2">Objetivo</flux:label>
                        <flux:input id="form-objective" wire:model="form.objective" />
                    </div>

                    @foreach (PdiItem::QUESTIONS as $field => $label)
                        <div class="grid gap-1.5 sm:grid-cols-[11rem_1fr] sm:gap-6">
                            <flux:label for="form-{{ $field }}" class="sm:pt-2">{{ $label }}</flux:label>
                            <flux:textarea id="form-{{ $field }}" wire:model="form.{{ $field }}" rows="3" />
                        </div>
                    @endforeach

                    <div class="grid gap-1.5 sm:grid-cols-[11rem_1fr] sm:gap-6">
                        <flux:label for="form-deadline" class="sm:pt-2">Prazo</flux:label>
                        <flux:input id="form-deadline" wire:model="form.deadline" />
                    </div>
                </div>
            </section>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">Salvar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
