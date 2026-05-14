<?php

namespace App\Filament\Widgets\ResourceStats;

use App\Models\CashFlow;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CashFlowStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $tokoId = $user?->toko_id;

        if (! filled($tokoId)) {
            return [];
        }

        $baseCashFlow = CashFlow::query()->where('toko_id', $tokoId);
        $baseTransactions = \App\Models\Transaction::query()->where('toko_id', $tokoId)->where('status', 'completed');

        // Sum manual income (excluding those already linked to transactions to avoid double counting)
        $manualIncome = (clone $baseCashFlow)
            ->where('type', 'income')
            ->where(fn($q) => $q->whereNull('reference_type')->orWhere('reference_type', '!=', 'transaction'))
            ->sum('amount');
        
        // Sum all completed sales from Transaction table
        $salesIncome = $baseTransactions->sum('total_amount');

        $income = $manualIncome + $salesIncome;
        $expense = (clone $baseCashFlow)->where('type', 'expense')->sum('amount');
        $balance = $income - $expense;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($income, 0, ',', '.'))
                ->description('Penjualan + Pemasukan Manual')
                ->color('success'),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->description('Semua biaya keluar')
                ->color('danger'),
            Stat::make('Saldo Kas Bersih', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description('Estimasi dana tersedia')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
