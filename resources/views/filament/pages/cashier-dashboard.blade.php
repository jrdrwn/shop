<x-filament-panels::page>
    <div class="space-y-5">

        {{-- ─── Hero Banner ─────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 p-5 text-white shadow-xl">
            <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/5 blur-3xl"></div>

            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-sky-300"></span>
                        Shift Aktif · Kasir
                    </div>
                    <h1 class="text-xl font-bold md:text-2xl">Halo, {{ Auth::user()?->name ?? 'Kasir' }}!</h1>
                    <p class="mt-1 text-sm text-white/80">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-xs font-medium">
                        <span class="h-2 w-2 rounded-full bg-sky-300"></span>
                        {{ $this->cafeName ?? 'Cafe belum ditetapkan' }}
                    </div>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-center backdrop-blur">
                    <p class="text-xs text-white/70">Waktu sekarang</p>
                    <p class="mt-1 text-2xl font-bold">{{ now()->format('H:i') }}</p>
                    <p class="text-xs text-white/60">WIB</p>
                </div>
            </div>
        </section>

        {{-- ─── Shift Stats ─────────────────────────────────────────── --}}
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($this->statsCards as $card)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ─── Hourly Chart + Today Transactions ───────────────────── --}}
        <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">

            {{-- Hourly Transactions --}}
            <x-filament::section>
                <x-slot name="heading">Distribusi Transaksi Per Jam</x-slot>
                <x-slot name="description">Jam sibuk shift hari ini berdasarkan jumlah transaksi.</x-slot>

                @php
                    $activeHours = array_filter($this->hourlyTransactions, fn($h) => $h['value'] > 0);
                @endphp

                @if (count($activeHours) > 0)
                    <div class="space-y-1.5">
                        @foreach ($this->hourlyTransactions as $point)
                            @if ($point['value'] > 0 || (int) substr($point['label'], 0, 2) >= 6)
                                <div class="flex items-center gap-3">
                                    <span class="w-12 shrink-0 text-right text-xs text-gray-500 dark:text-gray-400">{{ $point['label'] }}</span>
                                    <div class="relative h-5 flex-1 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800">
                                        @if ($point['value'] > 0)
                                            <div
                                                class="h-full rounded-md bg-sky-400 dark:bg-sky-600 transition-all"
                                                style="width: {{ $point['width'] }}%"
                                            ></div>
                                        @endif
                                    </div>
                                    <span class="w-8 shrink-0 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $point['value'] > 0 ? $point['value'] : '' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada transaksi hari ini.
                    </div>
                @endif
            </x-filament::section>

            {{-- Today Transactions List --}}
            <x-filament::section>
                <x-slot name="heading">Transaksi Saya Hari Ini</x-slot>
                <x-slot name="description">Daftar transaksi yang Anda proses pada shift ini.</x-slot>

                @if (count($this->todayTransactions) > 0)
                    <div class="space-y-2">
                        @foreach ($this->todayTransactions as $transaction)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div>
                                    <p class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $transaction['number'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $transaction['time'] }}</p>
                                </div>
                                <div class="text-right">
                                    <x-filament::badge :color="$transaction['status_color']">{{ $transaction['status'] }}</x-filament::badge>
                                    <p class="mt-0.5 text-sm font-bold text-gray-950 dark:text-white">{{ $transaction['total'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada transaksi yang diproses hari ini.
                    </div>
                @endif
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>
