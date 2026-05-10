<?php

namespace App\Filament\Resources\InventoryLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cafe.name')
                    ->label('Cafe')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn() => auth()->user()?->role === 'manager'),
                TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                TextColumn::make('action')->badge(),
                TextColumn::make('quantity_change')->sortable(),
                TextColumn::make('quantity_after')->sortable(),
                TextColumn::make('creator.name')->label('Created By'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->headerActions([
                \Filament\Actions\Action::make('export_csv')
                    ->label('Ekspor CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(function () {
                        $user = auth()->user();
                        if ($user?->role === 'super_admin') {
                            return true;
                        }
                        
                        $cafe = $user?->cafe;
                        if (!$cafe) {
                            return false;
                        }
                        
                        return app(\App\Services\SubscriptionService::class)->canExportReports($cafe);
                    })
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()->get();
                        
                        $filename = 'inventory-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';

                        return response()->streamDownload(function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Cafe', 'Product', 'Action', 'Change', 'After', 'Created By', 'Date']);

                            foreach ($records as $row) {
                                fputcsv($file, [
                                    $row->cafe?->name,
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
                    })
            ]);
    }
}
