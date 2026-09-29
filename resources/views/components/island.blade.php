@props([
    'title',
    'icon' => null,
    'height' => 'h-[26rem]',
])

{{-- Ilha: cartão de altura fixa com cabeçalho estático e corpo rolável. --}}
<section {{ $attributes->class(['border-outline-variant/40 bg-surface-container-lowest flex min-h-0 flex-col overflow-hidden rounded-2xl border shadow-sm', $height]) }}>
    <header class="border-outline-variant/30 flex shrink-0 items-center gap-3 border-b px-5 py-3.5">
        @if ($icon)
            <flux:icon :name="$icon" class="text-on-surface-variant size-5 shrink-0" />
        @endif

        <flux:heading class="flex-1 truncate">{{ $title }}</flux:heading>

        @isset($actions)
            <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
        @endisset
    </header>

    <div class="min-h-0 flex-1 overflow-y-auto p-5">
        {{ $slot }}
    </div>
</section>
