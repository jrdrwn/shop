<?php

namespace App\Filament\Resources\PaymentMethods\Pages;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    use ResolvesSubscription;

    protected static string $resource = PaymentMethodResource::class;

    public function mount(): void
    {
        parent::mount();

        $toko = static::tokoForCurrentUser();
        if (! $toko) {
            return;
        }

        $service = static::subscriptionService();

        if (! $service->canAddPaymentMethod($toko)) {
            $max = $toko->subscription?->getLimit('max_payment_methods');
            Notification::make()
                ->warning()
                ->title('Batas metode pembayaran tercapai')
                ->body("Paket Anda ({$max} metode) sudah penuh. Upgrade ke paket yang lebih tinggi untuk menambah metode pembayaran.")
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
