<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ─── Hero Banner ─────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 p-6 text-white shadow-2xl">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-8 left-1/3 h-40 w-64 rounded-full bg-white/5 blur-3xl"></div>

            <div class="relative flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                        Owner Dashboard
                    </div>
                    <h1 class="text-2xl font-bold leading-snug md:text-3xl">
                        Selamat datang di kontrol panel owner Anda
                    </h1>
                    <p class="max-w-lg text-sm leading-relaxed text-white/80">
                        Pantau performa penjualan, produk terlaris, stok menipis, dan kinerja tim kasir — semuanya real-time.
                    </p>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-sm font-medium backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        {{ $this->tokoName ?? 'Toko belum ditetapkan' }}
                    </div>
                </div>

                {{-- Monthly comparison --}}
                @if (!empty($this->monthlyComparison))
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 text-center backdrop-blur">
                        <p class="text-xs text-white/70">Bulan Ini vs Bulan Lalu</p>
                        <p class="mt-1 text-xl font-bold">{{ $this->monthlyComparison['this_month'] }}</p>
                        <div class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $this->monthlyComparison['is_positive'] ? 'bg-emerald-400/20 text-emerald-200' : 'bg-red-400/20 text-red-200' }}">
                            {{ $this->monthlyComparison['is_positive'] ? '▲' : '▼' }}
                            {{ abs($this->monthlyComparison['growth']) }}%
                        </div>
                        <p class="mt-1 text-xs text-white/60">Bulan lalu: {{ $this->monthlyComparison['last_month'] }}</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ─── Stats Row ──────────────────────────────────────────── --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->statsCards as $card)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs {{ isset($card['trend']) && $card['trend'] !== null ? ($card['trend'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500') : 'text-gray-400' }}">
                        {{ $card['hint'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- ─── Revenue Trend + Top Products ─────────────────────── --}}
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">

            {{-- Revenue Trend (14 days) --}}
            <x-filament::section>
                <x-slot name="heading">Tren Pendapatan 14 Hari</x-slot>
                <x-slot name="description">Pendapatan harian dari transaksi completed.</x-slot>

                @if (count($this->revenueTrend) > 0)
                    <div class="space-y-2">
                        @foreach ($this->revenueTrend as $point)
                            <div class="flex items-center gap-3">
                                <span class="w-12 shrink-0 text-right text-xs text-gray-500 dark:text-gray-400">{{ $point['label'] }}</span>
                                <div class="relative h-6 flex-1 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                                    @if ($point['width'] > 0)
                                        <div
                                            class="h-full rounded-lg {{ $point['is_today'] ? 'bg-emerald-500' : 'bg-emerald-300/70 dark:bg-emerald-700' }} transition-all"
                                            style="width: {{ $point['width'] }}%"
                                        ></div>
                                    @endif
                                </div>
                                <span class="w-32 shrink-0 text-right text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ $point['value'] > 0 ? $point['formatted'] : '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada data omzet.
                    </div>
                @endif
            </x-filament::section>

            {{-- Top Products --}}
            <x-filament::section>
                <x-slot name="heading">Produk Terlaris</x-slot>
                <x-slot name="description">Berdasarkan jumlah item terjual.</x-slot>

                @if (count($this->topProducts) > 0)
                    <div class="space-y-2">
                        @foreach ($this->topProducts as $index => $product)
                            <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-800 dark:bg-gray-800/50">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $product['revenue'] }}</p>
                                </div>
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ $product['qty'] }}×
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada data produk.
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- ─── Staff Performance + Low Stock + Today Transactions ─── --}}
        <div class="grid gap-6 xl:grid-cols-3">

            {{-- Staff Performance --}}
            <x-filament::section>
                <x-slot name="heading">Kinerja Kasir</x-slot>
                <x-slot name="description">Performa kasir aktif hari ini dan bulan ini.</x-slot>

                @if (count($this->staffPerformance) > 0)
                    <div class="space-y-3">
                        @foreach ($this->staffPerformance as $staff)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $staff['name'] }}</p>
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                        {{ $staff['today_count'] }} tx
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Omzet bulan ini: {{ $staff['month_revenue'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada kasir aktif.
                    </div>
                @endif
            </x-filament::section>

            {{-- Low Stock --}}
            <x-filament::section>
                <x-slot name="heading">Stok Menipis</x-slot>
                <x-slot name="description">Produk dengan stok ≤ 10 yang perlu perhatian.</x-slot>

                @if (count($this->lowStockProducts) > 0)
                    <div class="space-y-2">
                        @foreach ($this->lowStockProducts as $product)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sisa {{ $product['stock'] }} unit</p>
                                </div>
                                <x-filament::badge :color="$product['status_color']">{{ $product['status'] }}</x-filament::badge>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-green-600 dark:border-gray-700 dark:text-green-400">
                        ✓ Semua stok aman
                    </div>
                @endif
            </x-filament::section>

            {{-- Today's Transactions --}}
            <x-filament::section>
                <x-slot name="heading">Transaksi Hari Ini</x-slot>
                <x-slot name="description">10 transaksi terbaru dari tim kasir.</x-slot>

                @if (count($this->todayTransactions) > 0)
                    <div class="space-y-2">
                        @foreach ($this->todayTransactions as $transaction)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3 py-2.5 dark:border-gray-800">
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-mono text-gray-500 dark:text-gray-400">{{ $transaction['number'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $transaction['cashier'] }} · {{ $transaction['time'] }}</p>
                                </div>
                                <div class="ml-2 shrink-0 text-right">
                                    <x-filament::badge :color="$transaction['status_color']">{{ $transaction['status'] }}</x-filament::badge>
                                    <p class="mt-0.5 text-xs font-semibold text-gray-800 dark:text-white">{{ $transaction['total'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada transaksi hari ini.
                    </div>
                @endif
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>
