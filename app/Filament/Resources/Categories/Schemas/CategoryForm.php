<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Kategori')
                    ->description('Atur kategori agar mudah dipilih di produk dan POS.')
                    ->columns(2)
                    ->schema([
                        Select::make('cafe_id')
                            ->label('Cafe')
                            ->relationship('cafe', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn () => Auth::user()->role !== 'manager')
                            ->hidden(fn () => Auth::user()->role === 'manager'),
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->placeholder('Contoh: Makanan Berat')
                            ->maxLength(255),
                        TextInput::make('display_order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Kategori nonaktif tidak dipakai pada daftar input produk.'),
                    ]),
                Section::make('Tampilan Kategori')
                    ->description('Tambahkan visual untuk membantu operator mengenali kategori dengan cepat.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Gambar')
                            ->image()
                            ->directory('categories')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
