<?php

namespace App\Filament\Resources\DailyReports;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\DailyReports\Pages\ListDailyReports;
use App\Filament\Resources\DailyReports\Pages\ViewDailyReport;
use App\Filament\Resources\DailyReports\Schemas\DailyReportForm;
use App\Filament\Resources\DailyReports\Tables\DailyReportsTable;
use App\Models\DailyReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DailyReportResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = DailyReport::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $pluralModelLabel = 'Laporan Harian';

    protected static ?string $modelLabel = 'Laporan Harian';

    protected static ?string $roleNavigationGroup = 'Keuangan';

    protected static array $allowedRoles = ['owner'];

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

        if ($user?->role === 'owner' && filled($user->toko_id)) {
            return $query->where('toko_id', $user->toko_id);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
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
            'view' => ViewDailyReport::route('/{record}'),
        ];
    }
}
