<x-filament-widgets::widget>
    <div class="fi-subscription-upgrade">
        <x-filament::section
            heading="Pilih Paket Langganan"
            description="Tingkatkan toko Anda dengan paket yang sesuai."
            icon="heroicon-o-rocket-launch"
            collapsible
            collapsed
        >
            @php
                $currentPlan = $this->getCurrentPlan();
                $plans = $this->getPlans();
            @endphp

            @if ($currentPlan)
                <div class="fi-subscription-upgrade__current">
                    <p class="fi-subscription-upgrade__current-copy">
                        Paket aktif saat ini:
                        <x-filament::badge :color="$currentPlan['color']">
                            {{ $currentPlan['name'] }}
                        </x-filament::badge>
                    </p>

                    @if (isset($currentPlan['expiry_seconds']) && $currentPlan['expiry_seconds'] > 0)
                        <div
                            x-data="{
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
                            class="fi-subscription-upgrade__countdown"
                        >
                            Sisa Waktu: <span x-text="formatTime()"></span>
                        </div>
                    @else
                        <div class="fi-subscription-upgrade__expired">
                            Masa Aktif: Selamanya / Tidak Terbatas
                        </div>
                    @endif
                </div>
            @endif

            <div class="fi-subscription-upgrade__plans">
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

                    <div
                        class="fi-subscription-upgrade__plan {{ $isCurrent ? 'fi-subscription-upgrade__plan--current' : '' }}"
                        style="border-color: {{ $isCurrent ? $colorVar : 'transparent' }};"
                    >
                        @if ($isCurrent)
                            <div
                                class="fi-subscription-upgrade__current-badge"
                                style="background-color: {{ $colorVar }}"
                            >
                                Aktif
                            </div>
                        @endif

                        <div class="fi-subscription-upgrade__plan-header">
                            <h3 class="fi-subscription-upgrade__plan-name">
                                {{ $planData['name'] }}
                            </h3>
                            <x-filament::badge :color="$color">
                                {{ $planData['plan']->getLabel() }}
                            </x-filament::badge>
                        </div>

                        <div class="fi-subscription-upgrade__plan-price-wrap">
                            <span class="fi-subscription-upgrade__plan-price">
                                Rp {{ number_format($planData['price'], 0, ',', '.') }}
                            </span>
                            <span class="fi-subscription-upgrade__plan-duration">
                                / {{ $planData['duration_months'] > 0 ? $planData['duration_months'].' bulan' : 'selamanya' }}
                            </span>
                        </div>

                        <p class="fi-subscription-upgrade__plan-description">
                            {{ $planData['plan']->description() }}
                        </p>

                        <ul class="fi-subscription-upgrade__features">
                            @foreach ($planData['features'] as $feature)
                                <li class="fi-subscription-upgrade__feature">
                                    <x-filament::icon
                                        icon="heroicon-m-check-circle"
                                        class="h-4 w-4"
                                        style="color: {{ $colorVar }}"
                                    />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <div class="fi-subscription-upgrade__summary">
                            <p class="fi-subscription-upgrade__summary-title">Batas Penggunaan:</p>
                            <ul class="fi-subscription-upgrade__limits-list">
                                <li>Produk: {{ is_null($planData['limits']['max_products']) ? 'Tidak terbatas' : $planData['limits']['max_products'] }}</li>
                                <li>Kategori: {{ is_null($planData['limits']['max_categories']) ? 'Tidak terbatas' : $planData['limits']['max_categories'] }}</li>
                                <li>Staff: {{ is_null($planData['limits']['max_staff']) ? 'Tidak terbatas' : $planData['limits']['max_staff'] }}</li>
                                <li>Metode Pembayaran: {{ is_null($planData['limits']['max_payment_methods']) ? 'Tidak terbatas' : $planData['limits']['max_payment_methods'] }}</li>
                            </ul>
                        </div>

                        <div class="fi-subscription-upgrade__advanced">
                            <p class="fi-subscription-upgrade__advanced-title">Fitur Lanjutan:</p>
                            <ul class="fi-subscription-upgrade__advanced-list">
                                <li class="fi-subscription-upgrade__feature">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_export_reports'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5"
                                        style="color: {{ $planData['limits']['can_export_reports'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Ekspor Laporan
                                </li>
                                <li class="fi-subscription-upgrade__feature">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_inventory'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5"
                                        style="color: {{ $planData['limits']['can_use_inventory'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Manajemen Inventori
                                </li>
                                <li class="fi-subscription-upgrade__feature">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_variants'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5"
                                        style="color: {{ $planData['limits']['can_use_variants'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Varian Produk
                                </li>
                                <li class="fi-subscription-upgrade__feature">
                                    <x-filament::icon
                                        icon="{{ $planData['limits']['can_use_discounts'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle' }}"
                                        class="h-3.5 w-3.5"
                                        style="color: {{ $planData['limits']['can_use_discounts'] ? $colorVar : 'var(--color-gray-400)' }}"
                                    />
                                    Diskon Produk
                                </li>
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            @php
                $stats = $this->getStatusStats();
            @endphp

            @if (count($stats) > 0)
                <div class="fi-subscription-upgrade__stats-section">
                    <h4 class="fi-subscription-upgrade__stats-heading">Status Penggunaan & Fitur</h4>
                    <div class="fi-subscription-upgrade__stats-grid">
                        @foreach ($stats as $stat)
                            <div class="fi-subscription-upgrade__stats-item">
                                <x-filament::icon :icon="$stat['icon']" class="h-6 w-6" style="color: var(--color-{{ $stat['color'] }}-500)" />
                                <div>
                                    <p class="fi-subscription-upgrade__stats-label">{{ $stat['label'] }}</p>
                                    <p class="fi-subscription-upgrade__stats-value">{{ $stat['value'] }}</p>
                                    <p class="fi-subscription-upgrade__stats-label">{{ $stat['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="fi-subscription-upgrade__actions">
                {{ $this->selectPlanAction() }}
            </div>
        </x-filament::section>

        <div class="fi-subscription-upgrade__hooks" x-data="{
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
    </div>
</x-filament-widgets::widget>
