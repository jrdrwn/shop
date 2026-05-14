<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SuperAdminSubscriptionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Subscription Plan';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'super_admin';
    }

    protected function getData(): array
    {
        $subscriptions = Subscription::query()
            ->where('is_active', true)
            ->withCount('tokos')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Toko',
                    'data' => $subscriptions->pluck('tokos_count')->toArray(),
                    'backgroundColor' => ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'],
                ],
            ],
            'labels' => $subscriptions->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
