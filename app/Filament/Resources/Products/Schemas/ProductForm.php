<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Media & Identitas Produk')
                    ->description('Lengkapi identitas produk agar mudah dicari dan tampil rapi di POS.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_url')
                            ->label('Gambar')
                            ->image()
                            ->imageEditor()
                            ->imageAspectRatio('1:1')
                            ->automaticallyOpenImageEditorForAspectRatio()
                            ->visibility('public')
                            ->openable()
                            ->disk('public')
                            ->maxSize(2048) // 1MB
                            ->directory('products')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->placeholder('Contoh: Nasi Goreng Spesial')
                            ->maxLength(255),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->placeholder('SKU-001')
                            ->maxLength(100),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Item yang tidak aktif tidak muncul di POS.'),
                    ]),
                Section::make('Harga & Stok')
                    ->description('Harga, diskon, modal, dan stok dipakai untuk penjualan serta laporan.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga Jual')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        TextInput::make('discount_percentage')
                            ->label('Diskon Produk (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->suffix('unit')
                            ->minValue(0)
                            ->default(0),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),
                Section::make('Varian Produk')
                    ->description('Aktifkan varian jika produk tersedia dalam beberapa pilihan seperti ukuran atau suhu. Kasir akan diminta memilih sebelum menambah ke keranjang.')
                    ->schema([
                        Toggle::make('has_variants')
                            ->label('Produk memiliki varian')
                            ->helperText('Aktifkan jika ada pilihan ukuran, suhu, atau jenis lainnya.')
                            ->live()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TagsInput::make('variants.size')
                                    ->label('Pilihan Ukuran')
                                    ->placeholder('Tambah ukuran, tekan Enter...')
                                    ->suggestions(['Regular', 'Large', 'Small', 'Extra Large'])
                                    ->helperText('Contoh: Regular, Large'),
                                TagsInput::make('variants.temp')
                                    ->label('Pilihan Suhu')
                                    ->placeholder('Tambah suhu, tekan Enter...')
                                    ->suggestions(['Hot', 'Ice', 'Warm'])
                                    ->helperText('Contoh: Hot, Ice'),
                            ])
                            ->visible(fn($get) => (bool) $get('has_variants')),
                    ]),
            ]);
    }
}
