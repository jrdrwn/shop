<?php

namespace App\Filament\Resources\Tokos\Pages;

use App\Filament\Resources\Tokos\TokoResource;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ViewToko extends ViewRecord
{
    protected static string $resource = TokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startTrial')
                ->label('Aktifkan Trial 30 Hari')
                ->icon('heroicon-o-clock')
                ->authorize(fn (): bool => Auth::user()?->role === 'super_admin')
                ->visible(fn (): bool => Auth::user()?->role === 'super_admin'
                    && app(SubscriptionService::class)->canStartTrial($this->getRecord()))
                ->requiresConfirmation()
                ->modalDescription('Berikan akses Premium selama 30 hari. Setelah trial berakhir, paket kembali ke Free.')
                ->action(function (SubscriptionService $subscriptions): void {
                    abort_unless(Auth::user()?->role === 'super_admin', 403);

                    if (! $subscriptions->startTrial($this->getRecord())) {
                        Notification::make()
                            ->title('Trial tidak dapat diaktifkan')
                            ->body('Pastikan paket Free tersedia, Premium aktif, dan akun belum memiliki paket berbayar atau trial aktif.')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Trial Premium 30 hari aktif')
                        ->body('Berakhir pada '.$this->getRecord()->trial_ends_at->format('d-m-Y H:i').'.')
                        ->success()
                        ->send();
                }),

            // Ganti Password — only for Owner (to change their own password)
            Action::make('ganti_password')
                ->label('Ganti Password')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->role === 'owner')
                ->form([

                    TextInput::make('new_password')
                        ->label('Password Baru')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),
                    TextInput::make('new_password_confirmation')
                        ->label('Konfirmasi Password Baru')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    Auth::user()->update([
                        'password' => Hash::make($data['new_password']),
                    ]);

                    Notification::make()
                        ->title('Password berhasil diubah')
                        ->success()
                        ->send();
                }),

            // Edit Toko — only visible when Owner can edit this record
            EditAction::make()
                ->visible(fn (): bool => TokoResource::canEdit($this->getRecord())),
        ];
    }
}
