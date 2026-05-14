<?php

namespace App\Filament\Resources\InventoryLogs\Tables;

use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('toko.name')
                    ->label('Toko')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn () => auth()->user()?->role === 'owner'),
                TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('action')->badge(),
                TextColumn::make('quantity_change')->sortable(),
                TextColumn::make('quantity_after')->sortable(),
                TextColumn::make('creator.name')->label('Created By'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->headerActions([
                Action::make('export_csv')
                    ->label('Ekspor CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(function () {
                        $user = auth()->user();
                        if ($user?->role === 'super_admin') {
                            return true;
                        }

                        $toko = $user?->toko;
                        if (! $toko) {
                            return false;
                        }

                        return app(SubscriptionService::class)->canExportReports($toko);
                    })
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();

                        $filename = 'inventory-logs-'.now()->format('Y-m-d-H-i-s').'.csv';

                        return response()->streamDownload(function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Toko', 'Product', 'Action', 'Change', 'After', 'Created By', 'Date']);

                            foreach ($records as $row) {
                                fputcsv($file, [
                                    $row->toko?->name,
                                    $row->product?->name,
                                    $row->action,
                                    $row->quantity_change,
                                    $row->quantity_after,
                                    $row->creator?->name,
                                    $row->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }

                            fclose($file);
                        }, $filename);
                    }),
            ]);
    }
}
