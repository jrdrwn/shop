<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\BarcodeType;
use App\Models\Toko;
use App\Services\BarcodeService;
use App\Services\SubscriptionService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $canUseVariants = false;
        $canUseDiscounts = false;
        $canUseInventory = false;

        $user = Auth::user();
        if (in_array($user?->role, ['owner', 'gudang'], true) && filled($user->toko_id)) {
            $toko = Toko::find($user->toko_id);
            if ($toko) {
                $service = app(SubscriptionService::class);
                $canUseVariants = $service->canUseVariants($toko);
                $canUseDiscounts = $service->canUseDiscounts($toko);
                $canUseInventory = $service->canUseInventory($toko);
            }
        }

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
                            ->maxSize(2048)
                            ->directory('products')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->placeholder('Contoh: Nasi Goreng Spesial')
                            ->maxLength(255),
                        Select::make('barcode_type')
                            ->label('Jenis Barcode')
                            ->options(BarcodeType::options())
                            ->default(BarcodeType::Code128->value)
                            ->required()
                            ->live()
                            ->helperText('Pilih Code 128 (versi lama, bebas huruf/angka) atau EAN-13 (standar retail 13 digit angka).'),
                        TextInput::make('sku')
                            ->label('SKU / Kode Barcode')
                            ->placeholder(fn ($get): string => $get('barcode_type') === BarcodeType::Ean13->value ? 'Contoh: 8991234567893' : 'SKU-001')
                            ->maxLength(100)
                            ->live()
                            ->helperText(fn ($get): string => $get('barcode_type') === BarcodeType::Ean13->value
                                ? 'Masukkan 12 atau 13 digit angka EAN-13, atau klik tombol Generate di kanan.'
                                : 'Bisa diisi kode bebas (huruf, angka, tanda hubung).')
                            ->rule(function ($get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    if (! filled($value)) {
                                        return;
                                    }

                                    if ($get('barcode_type') === BarcodeType::Ean13->value) {
                                        $service = app(BarcodeService::class);
                                        if (! $service->isValidEan13((string) $value)) {
                                            $fail('Format EAN-13 tidak valid. Gunakan 12 atau 13 digit angka yang valid, atau klik tombol Generate EAN-13.');
                                        }
                                    }
                                };
                            })
                            ->suffixAction(
                                Action::make('generate_ean13')
                                    ->label('Generate EAN-13')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Buat kode barcode EAN-13 13 digit secara otomatis')
                                    ->action(function ($set): void {
                                        $set('barcode_type', BarcodeType::Ean13->value);
                                        $set('sku', app(BarcodeService::class)->generateEan13Code());
                                    })
                            ),
                        Placeholder::make('barcode_preview')
                            ->label('Preview Barcode')
                            ->columnSpanFull()
                            ->content(function ($get) {
                                $sku = trim((string) ($get('sku') ?? ''));
                                $type = BarcodeType::tryFrom((string) ($get('barcode_type') ?? BarcodeType::Code128->value)) ?? BarcodeType::Code128;

                                if ($sku === '') {
                                    return 'Isi SKU / Kode Barcode untuk melihat preview barcode.';
                                }

                                $service = app(BarcodeService::class);

                                if ($type === BarcodeType::Ean13 && ! $service->isValidEan13($sku)) {
                                    return new HtmlString(
                                        '<div style="padding: 8px 12px; border-radius: 8px; background: #fef3c7; color: #92400e; font-size: 0.85rem;">'
                                        .'⚠️ EAN-13 membutuhkan 12 atau 13 digit angka yang valid. Klik tombol <strong>Generate EAN-13</strong> atau ubah Jenis Barcode ke <strong>Code 128 (Versi Lama)</strong>.'
                                        .'</div>'
                                    );
                                }

                                try {
                                    $displaySku = $type === BarcodeType::Ean13 ? $service->normalizeEan13($sku) : $sku;
                                    $barcode = $service->generateBarcode($sku, 'png', $type);

                                    return new HtmlString(
                                        '<div style="display: inline-flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 14px; border-radius: 10px; background: #ffffff; border: 1px solid #e5e7eb;">'
                                        .'<span style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #059669;">'.$type->shortLabel().'</span>'
                                        .'<img src="data:image/png;base64,'.$barcode.'" alt="Barcode '.$type->shortLabel().'" style="max-height: 48px;" />'
                                        .'<span style="font-family: monospace; font-size: 0.8rem; color: #374151;">'.e($displaySku).'</span>'
                                        .'</div>'
                                    );
                                } catch (\Exception $e) {
                                    return 'Gagal generate barcode: '.$e->getMessage();
                                }
                            }),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) {
                                    $user = Auth::user();
                                    if (in_array($user?->role, ['owner', 'gudang'], true) && filled($user->toko_id)) {
                                        return $query->where('toko_id', $user->toko_id);
                                    }

                                    return $query;
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Item yang tidak aktif tidak muncul di POS.'),
                    ]),

                Section::make('Harga & Stok')
                    ->description('Harga, diskon, dan stok dipakai untuk penjualan serta laporan.')
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
                            ->maxValue(100)
                            ->visible($canUseDiscounts)
                            ->helperText($canUseDiscounts ? 'Diskon akan otomatis diterapkan di POS.' : null),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->suffix('unit')
                            ->minValue(0)
                            ->default(0)
                            ->visible($canUseInventory)
                            ->helperText($canUseInventory ? 'Jumlah stok saat ini.' : null),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                // Inventory locked notice
                Section::make('Manajemen Inventori')
                    ->description('Fitur manajemen stok tidak tersedia di paket Anda.')
                    ->visible(! $canUseInventory)
                    ->schema([
                        Placeholder::make('inventory_locked')
                            ->label('')
                            ->content('🔒 Fitur manajemen stok & inventori tersedia di paket Medium. Upgrade paket Anda untuk mengaktifkan fitur ini.'),
                    ]),

                // Discount locked notice (visible only when discount feature is locked)
                Section::make('Diskon Produk')
                    ->description('Fitur diskon tidak tersedia di paket Anda.')
                    ->visible(! $canUseDiscounts)
                    ->schema([
                        Placeholder::make('discount_locked')
                            ->label('')
                            ->content('🔒 Fitur diskon tersedia di paket Medium. Upgrade paket Anda untuk mengaktifkan diskon per produk.'),
                    ]),

                Section::make('Varian Produk')
                    ->description(
                        $canUseVariants
                            ? 'Aktifkan varian jika produk tersedia dalam beberapa pilihan seperti ukuran atau suhu.'
                            : 'Fitur varian tidak tersedia di paket Anda.'
                    )
                    ->schema(
                        $canUseVariants
                            ? [
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
                                    ->visible(fn ($get) => (bool) $get('has_variants')),
                            ]
                            : [
                                Placeholder::make('variants_locked')
                                    ->label('')
                                    ->content('🔒 Fitur varian produk tersedia di paket Medium. Upgrade paket Anda untuk mengaktifkan varian ukuran, suhu, dan lainnya.'),
                            ]
                    ),
            ]);
    }
}
