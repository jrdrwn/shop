<?php

namespace App\Filament\Resources\Cafes\Pages;

use App\Filament\Resources\Cafes\CafeResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ViewCafe extends ViewRecord
{
    protected static string $resource = CafeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Ganti Password — only for manager (to change their own password)
            Action::make('ganti_password')
                ->label('Ganti Password')
                ->icon('heroicon-o-key')
                ->color('gray')
                ->visible(fn (): bool => Auth::user()?->role === 'manager')
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

            // Edit Cafe — only visible when manager can edit this record
            EditAction::make()
                ->visible(fn (): bool => CafeResource::canEdit($this->getRecord())),
        ];
    }
}
