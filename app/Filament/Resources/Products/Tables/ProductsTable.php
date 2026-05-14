<?php

namespace App\Filament\Resources\Products\Tables;

use App\Services\BarcodeService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->circular()
                    ->disk('public')
                    ->imageSize(32),
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn($state): string => (string) $state)
                    ->color(fn($state): string => (int) $state <= 5 ? 'warning' : 'success'),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn($state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
                BadgeColumn::make('has_variants')
                    ->label('Varian')
                    ->formatStateUsing(fn($state): string => $state ? 'Ada Varian' : '-')
                    ->colors([
                        'primary' => true,
                        'gray' => false,
                    ]),
            ])
            ->groups([
                Group::make('category.name')
                    ->label('Kategori'),
            ])
            ->filters([
                // future filters (category, price range)
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('generate_barcode')
                    ->label('Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->modalContent(fn($record) => view('barcode.label', [
                        'barcode' => app(BarcodeService::class)->generateBarcode($record->sku ?? 'SKU', 'svg'),
                        'productName' => $record->name,
                        'sku' => $record->sku ?? 'SKU',
                        'price' => $record->price,
                        'storeName' => $record->toko?->name ?? 'Toko',
                        'format' => 'svg',
                    ]))
                    ->modalWidth('sm')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions([
                        Action::make('print_barcode')
                            ->label('Cetak')
                            ->icon('heroicon-o-printer')
                            ->color('success')
                            ->action(fn () => null)
                            ->extraAttributes([
                                'onclick' => "
                                    const printContents = document.getElementById('barcode-label-container').innerHTML;
                                    const printWindow = window.open('', '_blank');
                                    printWindow.document.write('<html><head><title>Print Barcode</title>');
                                    printWindow.document.write('<style>body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 90vh; margin: 0; } .label-container { border: 1px solid #000; padding: 20px; text-align: center; width: 250px; } .store-name { font-size: 10px; text-transform: uppercase; margin-bottom: 5px; } .product-name { font-weight: bold; margin-bottom: 10px; } .barcode-img { margin: 10px 0; } .sku { font-family: monospace; } .price { font-weight: bold; margin-top: 5px; }</style>');
                                    printWindow.document.write('</head><body>');
                                    printWindow.document.write(printContents);
                                    printWindow.document.write('</body></html>');
                                    printWindow.document.close();
                                    setTimeout(() => {
                                        printWindow.print();
                                        printWindow.close();
                                    }, 500);
                                ",
                            ]),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
