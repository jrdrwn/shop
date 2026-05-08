<?php

namespace App\Filament\Pages;

use App\Models\Cafe;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';

    public string $roleLabel = 'User';

    /** @var array<int, array{label: string, value: string}> */
    public array $statsCards = [];

    /** @var array<int, array{transaction_number: string, total: string, status: string, cashier: string}> */
    public array $recentTransactions = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->roleLabel = match ($user->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            default => 'User',
        };

        $this->statsCards = $this->buildStatsCards($user->role, $user->cafe_id);
        $this->recentTransactions = $this->buildRecentTransactions($user->role, $user->cafe_id, $user->id);
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    protected function buildStatsCards(string $role, ?int $cafeId): array
    {
        $todayQuery = Transaction::query()->whereDate('created_at', today());

        if ($role === 'super_admin') {
            return [
                ['label' => 'Langganan Aktif', 'value' => (string) Subscription::where('is_active', true)->count()],
                ['label' => 'Total Paket', 'value' => (string) Subscription::count()],
            ];
        }

        if ($role === 'admin') {
            return [
                ['label' => 'Total Cafe', 'value' => (string) Cafe::count()],
                ['label' => 'User Aktif', 'value' => (string) User::where('is_active', true)->count()],
                ['label' => 'Transaksi Hari Ini', 'value' => (string) (clone $todayQuery)->count()],
                ['label' => 'Omzet Hari Ini', 'value' => 'Rp '.number_format((int) (clone $todayQuery)->sum('total_amount'), 0, ',', '.')],
            ];
        }

        if ($role === 'manager' && filled($cafeId)) {
            $cafeTodayQuery = (clone $todayQuery)->where('cafe_id', $cafeId);

            return [
                ['label' => 'Produk Aktif Cafe', 'value' => (string) Product::where('cafe_id', $cafeId)->where('is_active', true)->count()],
                ['label' => 'Transaksi Hari Ini', 'value' => (string) (clone $cafeTodayQuery)->count()],
                ['label' => 'Omzet Hari Ini', 'value' => 'Rp '.number_format((int) (clone $cafeTodayQuery)->sum('total_amount'), 0, ',', '.')],
            ];
        }

        if ($role === 'cashier') {
            $cashierTodayQuery = (clone $todayQuery)->where('cashier_id', Auth::id());

            return [
                ['label' => 'Transaksi Saya Hari Ini', 'value' => (string) (clone $cashierTodayQuery)->count()],
                ['label' => 'Omzet Saya Hari Ini', 'value' => 'Rp '.number_format((int) (clone $cashierTodayQuery)->sum('total_amount'), 0, ',', '.')],
            ];
        }

        return [
            ['label' => 'Data', 'value' => '-'],
        ];
    }

    /**
     * @return array<int, array{transaction_number: string, total: string, status: string, cashier: string}>
     */
    protected function buildRecentTransactions(string $role, ?int $cafeId, int $userId): array
    {
        if ($role === 'super_admin') {
            return [];
        }

        $query = Transaction::query()->with('cashier:id,name');

        if ($role === 'manager' && filled($cafeId)) {
            $query->where('cafe_id', $cafeId);
        }

        if ($role === 'cashier') {
            $query->where('cashier_id', $userId);
        }

        return $query
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'transaction_number' => $transaction->transaction_number,
                'total' => 'Rp '.number_format((int) $transaction->total_amount, 0, ',', '.'),
                'status' => ucfirst($transaction->status),
                'cashier' => $transaction->cashier?->name ?? '-',
            ])
            ->all();
    }
}
