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
            'name' => 'Gudang',
            'url' => '/warehouse/login',
            'icon' => 'heroicon-o-archive-box',
            'description' => 'Manajemen Stok',
        ],
    ];

    if (!config('app.debug')) {
        $links = collect($links)->filter(fn($link) => $link['name'] !== 'Admin')->all();
    }
@endphp

<div class="fi-role-links">
    <div class="fi-role-links__heading">
        <p class="fi-role-links__heading-text">
            Login sebagai role lain?
        </p>
    </div>

    <div class="fi-role-links__list">
        @foreach ($links as $link)
            <a href="{{ $link['url'] }}" class="fi-role-link group">
                <div class="fi-role-link__content">
                    <div class="fi-role-link__icon">
                        <x-filament::icon :icon="$link['icon']" />
                    </div>
                    <div class="min-w-0">
                        <div class="fi-role-link__name">
                            {{ $link['name'] }}
                        </div>
                        <div class="fi-role-link__description">
                            {{ $link['description'] }}
                        </div>
                    </div>
                </div>
                <div class="fi-role-link__chevron">
                    <x-filament::icon icon="heroicon-m-arrow-right" />
                </div>
            </a>
        @endforeach
    </div>
</div>
