<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Widgets\ResourceStats\CategoryStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use ResolvesSubscription;

    protected static string $resource = CategoryResource::class;

    public function mount(): void
    {
        parent::mount();

        $toko = static::tokoForCurrentUser();
        if (! $toko) {
            return;
        }

        $service = static::subscriptionService();
        $remaining = $service->remainingCategories($toko);

        if ($remaining !== null && $remaining === 0) {
            $max = $toko->subscription?->getLimit('max_categories');
            Notification::make()
                ->warning()
                ->title('Batas kategori tercapai')
                ->body("Paket Anda ({$max} kategori) sudah penuh. Upgrade ke paket yang lebih tinggi untuk menambah lebih banyak kategori.")
                ->persistent()
                ->send();
        } elseif ($remaining !== null && $remaining <= 1) {
            Notification::make()
                ->warning()
                ->title('Sisa slot kategori: '.$remaining)
                ->body('Anda hampir mencapai batas kategori. Pertimbangkan untuk upgrade paket.')
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
            CategoryStatsWidget::class,
        ];
    }
}
