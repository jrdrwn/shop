@php
    $links = [
        [
            'name' => 'Admin',
            'url' => '/admin/login',
            'icon' => 'heroicon-o-shield-check',
            'description' => 'Kelola semua toko',
        ],
        [
            'name' => 'Owner',
            'url' => '/owner/login',
            'icon' => 'heroicon-o-building-storefront',
            'description' => 'Kelola toko Anda',
        ],
        [
            'name' => 'Kasir',
            'url' => '/cashier/login',
            'icon' => 'heroicon-o-calculator',
            'description' => 'Point of Sale',
        ],
    ];
@endphp

<div class="border-t border-gray-200 dark:border-white/10 mt-6 pt-4">
    <div class="text-center mb-4">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Login sebagai role lain?
        </p>
    </div>

    <div class="flex flex-col gap-2">
        @foreach ($links as $link)
            <a
                href="{{ $link['url'] }}"
                class="group flex items-center justify-between gap-3 rounded-xl border border-gray-200/80 dark:border-white/10 bg-white/60 dark:bg-white/5 px-4 py-3 transition-colors hover:border-primary-500/70 hover:bg-gray-50 dark:hover:bg-white/10 no-underline"
            >
                <div class="flex items-center gap-3">
                    <div class="shrink-0 text-gray-400 group-hover:text-primary-500 transition-colors">
                        <x-filament::icon
                            :icon="$link['icon']"
                            class="w-5 h-5"
                        />
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-sm text-gray-900 dark:text-white">
                            {{ $link['name'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $link['description'] }}
                        </div>
                    </div>
                </div>
                <div class="shrink-0 text-gray-300 group-hover:text-primary-500 transition-colors">
                    <x-filament::icon
                        icon="heroicon-m-arrow-right"
                        class="w-4 h-4"
                    />
                </div>
            </a>
        @endforeach
    </div>
</div>
