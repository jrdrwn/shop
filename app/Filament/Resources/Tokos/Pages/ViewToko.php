<?php

namespace App\Filament\Resources\Tokos\Pages;

use App\Filament\Resources\Tokos\TokoResource;
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
            // Ganti Password — only for Owner (to change their own password)
            Action::make('ganti_password')
                ->label('Ganti Password')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->role === 'owner')
                ->form([
                    TextInput::make('current_password')
                        ->label('Password Saat Ini')
                        ->password()
                        ->revealable()
                        ->required()
                        ->currentPassword(),
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
