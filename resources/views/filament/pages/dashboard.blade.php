<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ─── Hero Banner ─────────────────────────────────────────── --}}
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 p-6 text-white shadow-2xl">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 left-1/2 h-40 w-96 -translate-x-1/2 rounded-full bg-amber-400/8 blur-3xl"></div>

            <div class="relative grid gap-6 lg:grid-cols-[1fr_auto]">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-amber-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                        Super Admin Console
                    </div>
                    <h1 class="text-2xl font-bold leading-snug md:text-3xl">
                        Pantau Seluruh Ekosistem Cafe
                    </h1>
                    <p class="max-w-xl text-sm leading-relaxed text-slate-300">
                        Visibilitas penuh atas seluruh cafe, penugasan manager, status subscription, dan arus transaksi lintas cabang — semuanya dari satu layar.
                    </p>
                </div>

                <div class="flex items-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-center backdrop-blur">
                        <p class="text-xs text-slate-400">Waktu server</p>
                        <p class="mt-1 text-lg font-semibold">{{ now()->format('d M Y') }}</p>
                        <p class="text-sm text-slate-400">{{ now()->format('H:i') }} WIB</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── Stats Row ──────────────────────────────────────────── --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->statsCards as $card)
                <div class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold text-gray-950 dark:text-white">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ─── Cafe Overview Table + Subscription Plans ───────────── --}}
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">

            {{-- Cafe Table --}}
            <x-filament::section>
                <x-slot name="heading">Daftar Cafe & Manager</x-slot>
                <x-slot name="description">Semua cafe yang terdaftar beserta manager dan status subscription-nya.</x-slot>

                @if (count($this->cafeSummaries) > 0)
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Cafe</th>
                                    <th class="px-4 py-3">Kota</th>
                                    <th class="px-4 py-3">Manager</th>
                                    <th class="px-4 py-3">Subscription</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->cafeSummaries as $row)
                                    <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">{{ $row['name'] }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row['city'] }}</td>
                                        <td class="px-4 py-3">
                                            @if ($row['manager'] === 'Belum ditetapkan')
                                                <span class="text-xs italic text-gray-400">Belum ditetapkan</span>
                                            @else
                                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $row['manager'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($row['subscription'] === '-')
                                                <span class="text-xs italic text-gray-400">Tidak ada</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium {{ $row['subscription_active'] ? 'bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $row['subscription_active'] ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                                    {{ $row['subscription'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$row['status_color']">{{ $row['status'] }}</x-filament::badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada cafe yang terdaftar di sistem.
                    </div>
                @endif
            </x-filament::section>

            {{-- Subscription Plans + Recent Transactions --}}
            <div class="space-y-6">

                {{-- Subscription Plans --}}
                <x-filament::section>
                    <x-slot name="heading">Subscription Plans</x-slot>
                    <x-slot name="description">Plan yang tersedia dan jumlah cafe pemakai.</x-slot>

                    @if (count($this->subscriptionSummaries) > 0)
                        <div class="space-y-3">
                            @foreach ($this->subscriptionSummaries as $plan)
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                                    <div>
                                        <p class="font-semibold text-gray-950 dark:text-white">{{ $plan['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $plan['price'] }} / {{ $plan['duration'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $plan['cafes'] }}</span>
                                        <p class="text-xs text-gray-400">cafe</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 px-6 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Belum ada subscription plan.
                        </div>
                    @endif
                </x-filament::section>

                {{-- Recent Transactions --}}
                <x-filament::section>
                    <x-slot name="heading">Transaksi Terbaru</x-slot>
                    <x-slot name="description">Aktivitas terakhir lintas semua cafe.</x-slot>

                    @if (count($this->recentTransactions) > 0)
                        <div class="space-y-2">
                            @foreach ($this->recentTransactions as $row)
                                <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-800">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-mono text-gray-500 dark:text-gray-400">{{ $row['number'] }}</p>
                                        <p class="mt-0.5 truncate font-medium text-gray-900 dark:text-white">{{ $row['cafe'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $row['cashier'] }} · {{ $row['time'] }}</p>
                                    </div>
                                    <div class="ml-4 shrink-0 text-right">
                                        <x-filament::badge :color="$row['status_color']">{{ $row['status'] }}</x-filament::badge>
                                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">{{ $row['total'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 px-6 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Belum ada transaksi.
                        </div>
                    @endif
                </x-filament::section>

            </div>
        </div>

    </div>
</x-filament-panels::page>
