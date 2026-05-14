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

<div style="border-top: 1px solid rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 1rem;">
        <p style="font-size: 0.875rem; font-weight: 500; opacity: 0.7;">
            Login sebagai role lain?
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
        @foreach ($links as $link)
            <a
                href="{{ $link['url'] }}"
                style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(0,0,0,0.2); text-decoration: none; transition: all 0.2s;"
                onmouseover="this.style.borderColor='var(--primary-500)'; this.style.backgroundColor='rgba(0,0,0,0.02)';"
                onmouseout="this.style.borderColor='rgba(0,0,0,0.2)'; this.style.backgroundColor='transparent';"
            >
                <div style="flex-shrink: 0;">
                    <x-filament::icon
                        :icon="$link['icon']"
                        class="fi-color-custom"
                        style="width: 1.5rem; height: 1.5rem; opacity: 0.6;"
                    />
                </div>
                <div style="flex: 1; text-align: left;">
                    <div style="font-weight: 600; font-size: 0.875rem; margin-bottom: 0.125rem;">
                        {{ $link['name'] }}
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.6;">
                        {{ $link['description'] }}
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    <x-filament::icon
                        icon="heroicon-m-arrow-right"
                        style="width: 1rem; height: 1rem; opacity: 0.4;"
                    />
                </div>
            </a>
        @endforeach
    </div>
</div>
