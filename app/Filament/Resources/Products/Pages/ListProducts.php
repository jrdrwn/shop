<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\ResourceStats\ProductStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use ResolvesSubscription;

    protected static string $resource = ProductResource::class;

    public function mount(): void
    {
        parent::mount();

        $toko = static::tokoForCurrentUser();
        if (! $toko) {
            return;
        }

        $service = static::subscriptionService();
        $remaining = $service->remainingProducts($toko);

        // Limit reached
        if ($remaining !== null && $remaining === 0) {
            $max = $toko->subscription?->getLimit('max_products');
            Notification::make()
                ->warning()
                ->title('Batas produk tercapai')
                ->body("Paket Anda ({$max} produk) sudah penuh. Upgrade ke paket yang lebih tinggi untuk menambah lebih banyak produk.")
                ->persistent()
                ->send();
        }

        // Near limit (≤ 2 slot tersisa)
        elseif ($remaining !== null && $remaining <= 2) {
            Notification::make()
                ->warning()
                ->title('Sisa slot produk: '.$remaining)
                ->body('Anda hampir mencapai batas produk. Pertimbangkan untuk upgrade paket.')
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStatsWidget::class,
        ];
    }
}
