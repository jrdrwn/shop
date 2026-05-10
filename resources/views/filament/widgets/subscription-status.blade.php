<x-filament-widgets::widget>
    <x-filament::section
        heading="Status Langganan"
        description="Penggunaan paket dan ketersediaan fitur untuk cafe Anda."
        icon="heroicon-o-credit-card"
        collapsible
        collapsed
    >
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($this->getStats() as $stat)
                @php
                    $colorVar = match ($stat['color']) {
                        'success'  => 'var(--color-success-500)',
                        'warning'  => 'var(--color-warning-500)',
                        'danger'   => 'var(--color-danger-500)',
                        'info'     => 'var(--color-info-500)',
                        'primary'  => 'var(--color-primary-500)',
                        default    => 'var(--color-gray-400)',
                    };
                @endphp

                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        {{ $stat['label'] }}
                    </p>

                    <p class="mt-2 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ $stat['value'] }}
                    </p>

                    <p class="mt-1 flex items-center gap-1.5 text-xs font-medium" style="color: {{ $colorVar }}">
                        <x-filament::icon
                            :icon="$stat['icon']"
                            class="h-3.5 w-3.5 shrink-0"
                        />
                        {{ $stat['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
