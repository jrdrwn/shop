<x-filament-widgets::widget>
    <x-filament::section
        heading="Pilih Paket Langganan"
        description="Tingkatkan cafe Anda dengan paket yang sesuai."
        icon="heroicon-o-rocket-launch"
        collapsible
        collapsed
    >
        @php
            $currentPlan = $this->getCurrentPlan();
            $plans = $this->getPlans();
        @endphp

        @if ($currentPlan)
            <div class="mb-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-800 flex justify-between items-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Paket aktif saat ini:
                    <x-filament::badge :color="$currentPlan['color']" class="ml-1">
                        {{ $currentPlan['name'] }}
                    </x-filament::badge>
                </p>
                
                @if (isset($currentPlan['expiry_seconds']) && $currentPlan['expiry_seconds'] > 0)
                    <div x-data="{ 
                        seconds: {{ $currentPlan['expiry_seconds'] }},
                        formatTime() {
                            const days = Math.floor(this.seconds / 86400);
                            const hours = Math.floor((this.seconds % 86400) / 3600);
                            const minutes = Math.floor((this.seconds % 3600) / 60);
                            const secs = this.seconds % 60;
                            return `${days}h ${hours}j ${minutes}m ${secs}d`;
                        }
                    }"
                    x-init="setInterval(() => { if (seconds > 0) seconds--; else window.location.reload(); }, 1000)"
                    class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                        Sisa Waktu: <span x-text="formatTime()"></span>
                    </div>
                @else
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                        Masa Aktif: Selamanya / Tidak Terbatas
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($plans as $planData)
                @php
                    $isCurrent = $currentPlan && $currentPlan['name'] === $planData['name'];
                    $color = $planData['color'];
                    $colorVar = match ($color) {
                        'success'  => 'var(--color-success-500)',
                        'warning'  => 'var(--color-warning-500)',
                        'danger'   => 'var(--color-danger-500)',
                        'info'     => 'var(--color-info-500)',
                        'primary'  => 'var(--color-primary-500)',
                        default    => 'var(--color-gray-400)',
                    };
                @endphp

                <div class="relative flex flex-col rounded-xl border-2 p-5 transition hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                    style="border-color: {{ $isCurrent ? $colorVar : 'transparent' }};">

                    @if ($isCurrent)
                        <div class="absolute -top-3 right-4 rounded-full px-3 py-0.5 text-xs font-semibold text-white"
                            style="background-color: {{ $colorVar }}">
                            Aktif
                        </div>
                    @endif

                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                            {{ $planData['name'] }}
                        </h3>
                        <x-filament::badge :color="$color">
                            {{ $planData['plan']->getLabel() }}
                        </x-filament::badge>
                    </div>

                    <div class="mb-4">
                        <span class="text-3xl font-extrabold tracking-tight text-gray-950 dark:text-white">
                            Rp {{ number_format($planData['price'], 0, ',', '.') }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            / {{ $planData['duration_months'] > 0 ? $planData['duration_months'].' bulan' : 'selamanya' }}
                        </span>
                    </div>

                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ $planData['plan']->description() }}
                    </p>

                    <ul class="mb-5 flex-1 space-y-2">
                        @foreach ($planData['features'] as $feature)
                            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <x-filament::icon
                                    icon="heroicon-m-check-circle"
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    style="color: {{ $colorVar }}"
                                />
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-auto space-y-2">
                        <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <p class="font-semibold mb-1">Batas Penggunaan:</p>
                            <ul class="space-y-1">
                                <li>Produk: {{ is_null($planData['limits']['max_products']) ? 'Tidak terbatas' : $planData['limits']['max_products'] }}</li>
                                <li>Kategori: {{ is_null($planData['limits']['max_categories']) ? 'Tidak terbatas' : $planData['limits']['max_categories'] }}</li>
                                <li>Staff: {{ is_null($planData['limits']['max_staff']) ? 'Tidak terbatas' : $planData['limits']['max_staff'] }}</li>
                                <li>Metode Pembayaran: {{ is_null($planData['limits']['max_payment_methods']) ? 'Tidak terbatas' : $planData['limits']['max_payment_methods'] }}</li>
                            </ul>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <p class="font-semibold mb-1">Fitur Lanjutan:</p>
                            <ul class="space-y-1">
                                <li class="flex items-center gap-1">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_export_reports'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5 shrink-0"
                                        style="color: {{ $planData['limits']['can_export_reports'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Ekspor Laporan
                                </li>
                                <li class="flex items-center gap-1">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_inventory'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5 shrink-0"
                                        style="color: {{ $planData['limits']['can_use_inventory'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Manajemen Inventori
                                </li>
                                <li class="flex items-center gap-1">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_variants'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5 shrink-0"
                                        style="color: {{ $planData['limits']['can_use_variants'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Varian Produk
                                </li>
                                <li class="flex items-center gap-1">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_discounts'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5 shrink-0"
                                        style="color: {{ $planData['limits']['can_use_discounts'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Diskon Produk
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $stats = $this->getStatusStats();
        @endphp
        
        @if (count($stats) > 0)
            <div class="mt-6 border-t pt-4 dark:border-gray-700">
                <h4 class="text-md font-semibold mb-3 text-gray-950 dark:text-white">Status Penggunaan & Fitur</h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800 flex items-center gap-3">
                            <x-filament::icon :icon="$stat['icon']" class="h-6 w-6" style="color: var(--color-{{ $stat['color'] }}-500)" />
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                                <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $stat['value'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stat['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-4 flex justify-end">
            {{ $this->selectPlanAction() }}
        </div>
    </x-filament::section>

    <div x-data="{
        token: @entangle('snapToken'),
        clientKey: @entangle('clientKey'),
        snapUrl: @entangle('snapUrl')
    }"
    x-init="$watch('token', value => {
        if (value) {
            if (!window.snap) {
                const script = document.createElement('script');
                script.src = snapUrl;
                script.setAttribute('data-client-key', clientKey);
                script.onload = () => {
                    window.snap.pay(value, {
                        onSuccess: function(result) {
                            window.location.href = '{{ route("subscription.finish") }}?order_id=' + encodeURIComponent(result.order_id) + '&status_code=200';
                        },
                        onPending: function(result) {
                            window.location.href = '{{ route("subscription.finish") }}?order_id=' + encodeURIComponent(result.order_id) + '&status_code=201';
                        },
                        onError: function(result) {
                            window.location.href = '{{ route("subscription.error") }}?order_id=' + encodeURIComponent(result.order_id || '');
                        },
                        onClose: function() {
                            token = null;
                        }
                    });
                };
                document.head.appendChild(script);
            } else {
                window.snap.pay(value, {
                    onSuccess: function(result) {
                        window.location.href = '{{ route("subscription.finish") }}?order_id=' + encodeURIComponent(result.order_id) + '&status_code=200';
                    },
                    onPending: function(result) {
                        window.location.href = '{{ route("subscription.finish") }}?order_id=' + encodeURIComponent(result.order_id) + '&status_code=201';
                    },
                    onError: function(result) {
                        window.location.href = '{{ route("subscription.error") }}?order_id=' + encodeURIComponent(result.order_id || '');
                    },
                    onClose: function() {
                        token = null;
                    }
                });
            }
        }
    })"></div>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
