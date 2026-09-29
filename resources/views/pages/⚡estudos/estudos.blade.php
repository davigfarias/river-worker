<div class="mx-auto w-full max-w-7xl py-8">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <flux:heading size="xl" level="1">Estudos de código</flux:heading>
            <flux:text class="mt-2">Refatorações e patterns dissecados passo a passo.</flux:text>
        </div>
        <flux:modal.trigger name="add-study"><flux:button icon="plus">Novo estudo</flux:button></flux:modal.trigger>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->studies as $study)
            <a wire:key="study-{{ $study->id }}" href="{{ route('estudos.show', $study->id) }}" wire:navigate class="group border-surface-variant bg-surface-container-lowest rounded-xl border p-4 shadow-sm transition-shadow hover:shadow-md">
                @if ($study->topic)
                    <flux:badge size="sm" color="zinc">{{ $study->topic }}</flux:badge>
                @endif
                <flux:heading class="group-hover:text-primary mt-3 leading-snug">{{ $study->title }}</flux:heading>
                <flux:text size="sm" class="text-on-surface-variant mt-3">
                    {{ $study->steps_count }} {{ $study->steps_count === 1 ? 'passo' : 'passos' }}
                    @if ($study->principle) &middot; {{ $study->principle->acronym ?? $study->principle->name }} @endif
                </flux:text>
            </a>
        @empty
            <x-empty-state icon="code-bracket-square" heading="Nenhum estudo" description="Crie o primeiro estudo split-view." class="col-span-full" />
        @endforelse
    </div>

    <flux:modal name="add-study" class="w-full max-w-[calc(100vw-2rem)] sm:max-w-lg">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">Novo estudo</flux:heading>
            <flux:input label="Título" wire:model="title" placeholder="Extraindo Strategy do CheckoutService" />
            <flux:input label="Tópico" wire:model="topic" placeholder="Ex: Strategy Pattern" />
            <flux:select label="Princípio" wire:model="principle_id" placeholder="Nenhum">
                @foreach ($this->principles as $principle)
                    <flux:select.option value="{{ $principle->id }}">{{ $principle->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex"><flux:spacer /><flux:button type="submit" variant="primary">Criar estudo</flux:button></div>
        </form>
    </flux:modal>
</div>
