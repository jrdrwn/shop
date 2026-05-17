<x-filament-widgets::widget>
    <x-filament::section
        heading="Status Langganan"
        description="Penggunaan paket dan ketersediaan fitur untuk toko Anda."
        icon="heroicon-o-credit-card"
        collapsible
        collapsed
    >
        <div class="fi-subscription-status">
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

                <div class="fi-subscription-status__stat">
                    <p class="fi-subscription-status__label">
                        {{ $stat['label'] }}
                    </p>

                    <p class="fi-subscription-status__value">
                        {{ $stat['value'] }}
                    </p>

                    <p class="fi-subscription-status__meta" style="color: {{ $colorVar }}">
                        <x-filament::icon
                            :icon="$stat['icon']"
                            class="fi-subscription-status__icon h-3.5 w-3.5"
                        />
                        {{ $stat['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
