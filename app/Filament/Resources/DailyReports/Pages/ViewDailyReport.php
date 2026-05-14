<?php

namespace App\Filament\Resources\DailyReports\Pages;

use App\Filament\Resources\DailyReports\DailyReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewDailyReport extends ViewRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            // Future widgets (charts) can be added here
        ];
    }
}
