<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_number')
                    ->label('Nomor Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.')),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function ($state): string {
                        return match ($state) {
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            default => (string) $state,
                        };
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('minggu_ini')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
                Filter::make('bulan_ini')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),
            ])
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

                        $filename = 'transaksi-'.now()->format('Y-m-d-H-i-s').'.csv';

                        return response()->streamDownload(function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['Nomor Transaksi', 'Kasir', 'Total', 'Status', 'Tanggal']);

                            foreach ($records as $row) {
                                fputcsv($file, [
                                    $row->transaction_number,
                                    $row->cashier?->name,
                                    $row->total_amount,
                                    $row->status,
                                    $row->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }

                            fclose($file);
                        }, $filename);
                    }),
            ]);
    }
}
