<?php

namespace App\Filament\Resources\DailyReports\Pages;

use App\Filament\Resources\DailyReports\DailyReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate')
                ->label('Generate Laporan Hari Ini')
                ->color('success')
                ->action(function () {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $toko_id = $user->toko_id;
                    $today = now()->toDateString();
                    
                    $transactions = \App\Models\Transaction::where('toko_id', $toko_id)
                        ->whereDate('created_at', $today)
                        ->where('status', 'completed')
                        ->with('payments.paymentMethod')
                        ->get();
                        
                    $total_sales = $transactions->sum('total_amount');
                    $total_discount = $transactions->sum('discount_amount');
                    $total_tax = $transactions->sum('tax_amount');
                    
                    $total_cash = 0;
                    $total_debit = 0;
                    $total_qris = 0;

                    foreach ($transactions as $transaction) {
                        foreach ($transaction->payments as $payment) {
                            $type = $payment->paymentMethod?->type;
                            if ($type === 'cash') {
                                $total_cash += $payment->amount;
                            } elseif ($type === 'debit') {
                                $total_debit += $payment->amount;
                            } elseif ($type === 'qris') {
                                $total_qris += $payment->amount;
                            }
                        }
                    }
                    
                    \App\Models\DailyReport::updateOrCreate(
                        ['toko_id' => $toko_id, 'report_date' => $today],
                        [
                            'total_transactions' => $transactions->count(),
                            'total_sales' => $total_sales,
                            'total_discount' => $total_discount,
                            'total_tax' => $total_tax,
                            'total_cash' => $total_cash,
                            'total_debit' => $total_debit,
                            'total_qris' => $total_qris,
                            'opening_balance' => 0,
                            'closing_balance' => $total_sales,
                            'created_by' => $user->id,
                        ]
                    );
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Laporan berhasil di-generate')
                        ->success()
                        ->send();
                }),
        ];
    }
}
