<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\BarcodeType;
use App\Services\BarcodeService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                    ->label('SKU / Barcode')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->description(fn ($record): string => (BarcodeType::tryFrom((string) ($record->barcode_type ?? BarcodeType::Code128->value)) ?? BarcodeType::Code128)->shortLabel()),
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state): string => (string) $state)
                    ->color(fn ($state): string => (int) $state <= 5 ? 'warning' : 'success'),
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn ($state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),
                BadgeColumn::make('has_variants')
                    ->label('Varian')
                    ->formatStateUsing(fn ($state): string => $state ? 'Ada Varian' : '-')
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
                    ->modalHeading('Label Barcode Produk')
                    ->modalContent(function ($record) {
                        $service = app(BarcodeService::class);
                        $sku = trim((string) ($record->sku ?: 'SKU-'.$record->id));
                        $savedType = BarcodeType::tryFrom((string) ($record->barcode_type ?? BarcodeType::Code128->value)) ?? BarcodeType::Code128;
                        $isEan13Valid = $service->isValidEan13($sku);

                        $code128Barcode = $service->generateBarcode($sku, 'svg', BarcodeType::Code128);
                        $ean13Barcode = $isEan13Valid ? $service->generateBarcode($sku, 'svg', BarcodeType::Ean13) : null;
                        $ean13Sku = $isEan13Valid ? $service->normalizeEan13($sku) : null;

                        $activeType = ($savedType === BarcodeType::Ean13 && $isEan13Valid)
                            ? BarcodeType::Ean13
                            : BarcodeType::Code128;

                        return view('barcode.label', [
                            'barcode' => $activeType === BarcodeType::Ean13 ? $ean13Barcode : $code128Barcode,
                            'barcodeType' => $activeType->value,
                            'code128Barcode' => $code128Barcode,
                            'ean13Barcode' => $ean13Barcode,
                            'ean13Sku' => $ean13Sku,
                            'productName' => $record->name,
                            'sku' => $sku,
                            'price' => $record->price,
                            'storeName' => $record->toko?->name ?? 'Toko',
                            'format' => 'svg',
                        ]);
                    })
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
                                    const card = document.querySelector('#barcode-label-container .fi-barcode-label__card');
                                    if (!card) return;
                                    const printWindow = window.open('', '_blank', 'width=420,height=420');
                                    if (!printWindow) { alert('Popup diblokir browser. Izinkan popup untuk mencetak barcode.'); return; }
                                    printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Barcode</title>');
                                    printWindow.document.write('<style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:90vh;margin:0;background:#fff;}.fi-barcode-label__card{border:1px solid #000;border-radius:6px;padding:16px;text-align:center;width:250px;background:#fff;}.fi-barcode-label__store{font-size:10px;text-transform:uppercase;letter-spacing:0.12em;color:#555;margin-bottom:4px;}.fi-barcode-label__product{font-weight:700;font-size:14px;color:#000;margin-bottom:8px;}.fi-barcode-label__barcode{display:flex;justify-content:center;margin:8px 0;}.fi-barcode-label__barcode svg,.fi-barcode-label__barcode img{max-width:100%;height:auto;}.fi-barcode-label__sku{font-family:monospace;font-size:13px;color:#111;margin-bottom:4px;}.fi-barcode-label__price{font-weight:700;font-size:15px;color:#000;}.fi-barcode-label__type{font-size:9px;text-transform:uppercase;letter-spacing:0.08em;color:#666;margin-bottom:4px;}</style>');
                                    printWindow.document.write('</head><body>');
                                    printWindow.document.write(card.outerHTML);
                                    printWindow.document.write('</body></html>');
                                    printWindow.document.close();
                                    setTimeout(() => {
                                        printWindow.focus();
                                        printWindow.print();
                                        printWindow.close();
                                    }, 350);
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
