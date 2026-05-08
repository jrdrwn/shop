<?php

namespace App\Filament\Resources\DailyReports;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\DailyReports\Pages\CreateDailyReport;
use App\Filament\Resources\DailyReports\Pages\EditDailyReport;
use App\Filament\Resources\DailyReports\Pages\ListDailyReports;
use App\Filament\Resources\DailyReports\Schemas\DailyReportForm;
use App\Filament\Resources\DailyReports\Tables\DailyReportsTable;
use App\Models\DailyReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReportResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = DailyReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['admin', 'manager'];

    protected static ?string $recordTitleAttribute = 'report_date';

    public static function form(Schema $schema): Schema
    {
        return DailyReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        if ($user?->role === 'manager' && filled($user->cafe_id)) {
            return $query->where('cafe_id', $user->cafe_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyReports::route('/'),
            'create' => CreateDailyReport::route('/create'),
            'edit' => EditDailyReport::route('/{record}/edit'),
        ];
    }
}
