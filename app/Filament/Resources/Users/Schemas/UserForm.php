<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isManager = Auth::user()?->role === 'manager';

        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->description('Data dasar akun yang akan tampil di sistem dan riwayat aktivitas.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->placeholder('Nama pengguna')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('nama@domain.com')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->placeholder('08xxxxxxxxxx')
                            ->tel()
                            ->maxLength(20),
                        Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->helperText('Nonaktifkan jika akun tidak boleh login sementara.'),
                    ]),

                Section::make('Hak Akses & Keamanan')
                    ->description('Atur role, cakupan cafe, dan kata sandi akun.')
                    ->columns(2)
                    ->schema([
                        // Manager: role fixed to cashier, hidden from UI
                        $isManager
                            ? Hidden::make('role')->default('cashier')
                            : Select::make('role')
                                ->label('Role')
                                ->options([
                                    'super_admin' => 'Super Admin',
                                    'manager' => 'Manager',
                                    'cashier' => 'Cashier',
                                ])
                                ->required()
                                ->helperText('Role menentukan menu dan data yang dapat diakses.'),

                        // Manager: cafe auto-filled from their own cafe, hidden from UI
                        $isManager
                            ? Hidden::make('cafe_id')->default(fn () => Auth::user()?->cafe_id)
                            : Select::make('cafe_id')
                                ->label('Cafe')
                                ->relationship('cafe', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Wajib untuk manager dan cashier yang terikat pada cafe tertentu.'),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->helperText('Kosongkan saat edit jika password tidak ingin diubah.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
