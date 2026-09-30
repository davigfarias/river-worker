@props([
    'model',
    'value',
])

<fieldset class="space-y-2">
    <legend class="text-sm font-medium">Natureza</legend>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        @foreach (\App\Enums\WorkItemKind::cases() as $kind)
            <label wire:key="kind-{{ $model }}-{{ $kind->value }}" title="{{ $kind->label() }}" class="has-checked:border-primary has-checked:bg-primary/10 has-focus-visible:ring-primary/50 border-surface-variant hover:bg-surface-container-low flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition-colors has-focus-visible:ring-2">
                <input type="radio" name="{{ $model }}-kind" value="{{ $kind->value }}" wire:model.live="{{ $model }}" class="sr-only" />
                <span class="size-2 shrink-0 rounded-full {{ $kind->dotClass() }}"></span>
                <span class="font-mono text-sm">{{ $kind->value }}</span>
            </label>
        @endforeach
    </div>
    <flux:text size="sm" class="min-h-5">{{ \App\Enums\WorkItemKind::tryFrom($value)?->label() }}</flux:text>
    <flux:error name="{{ $model }}" />
</fieldset>
