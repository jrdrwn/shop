<div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
    <div class="text-center mb-4">
        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
            Login sebagai role lain?
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach ($links as $link)
            <a
                href="{{ $link['url'] }}"
                class="flex items-center gap-3 p-4 rounded-lg border border-gray-300 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200 group"
            >
                <div class="flex-shrink-0">
                    <x-filament::icon
                        :icon="$link['icon']"
                        class="h-6 w-6 text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                    />
                </div>
                <div class="flex-1 text-left">
                    <div class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        {{ $link['name'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $link['description'] }}
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <x-filament::icon
                        icon="heroicon-m-arrow-right"
                        class="h-4 w-4 text-gray-400 group-hover:text-primary-500 group-hover:translate-x-1 transition-all duration-200"
                    />
                </div>
            </a>
        @endforeach
    </div>
</div>
