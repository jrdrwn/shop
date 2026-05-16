<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Toko;
use App\Services\BarcodeService;
use App\Services\SubscriptionService;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $canUseVariants = false;
        $canUseDiscounts = false;

        $user = Auth::user();
        if (in_array($user?->role, ['owner', 'gudang'], true) && filled($user->toko_id)) {
            $toko = Toko::find($user->toko_id);
            if ($toko) {
                $service = app(SubscriptionService::class);
                $canUseVariants = $service->canUseVariants($toko);
                $canUseDiscounts = $service->canUseDiscounts($toko);
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
                        TextInput::make('sku')
                            ->label('SKU')
                            ->placeholder('SKU-001')
                            ->maxLength(100)
                            ->live(),
                        Placeholder::make('barcode_preview')
                            ->label('Preview Barcode')
                            ->content(function ($get) {
                                $sku = $get('sku');
                                if (! $sku) {
                                    return 'Isi SKU untuk melihat barcode';
                                }
                                try {
                                    $barcode = app(BarcodeService::class)->generateBarcode($sku);

                                    return new HtmlString('<img src="data:image/png;base64,'.$barcode.'" alt="Barcode" style="max-height: 40px;" />');
                                } catch (\Exception $e) {
                                    return 'Gagal generate barcode';
                                }
                            }),
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
                            ->default(0),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                // Discount locked notice (visible only when discount feature is locked)
                Section::make('Diskon Produk')
                    ->description('Fitur diskon tidak tersedia di paket Anda.')
                    ->visible(! $canUseDiscounts)
                    ->schema([
                        Placeholder::make('discount_locked')
                            ->label('')
                            ->content('🔒 Fitur diskon tersedia di paket Pro. Upgrade paket Anda untuk mengaktifkan diskon per produk.'),
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
                                    ->content('🔒 Fitur varian produk tersedia di paket Pro. Upgrade paket Anda untuk mengaktifkan varian ukuran, suhu, dan lainnya.'),
                            ]
                    ),
            ]);
    }
}
