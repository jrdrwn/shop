<?php

namespace App\Filament\Resources\UserActivityLogs;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\UserActivityLogs\Pages\CreateUserActivityLog;
use App\Filament\Resources\UserActivityLogs\Pages\EditUserActivityLog;
use App\Filament\Resources\UserActivityLogs\Pages\ListUserActivityLogs;
use App\Filament\Resources\UserActivityLogs\Schemas\UserActivityLogForm;
use App\Filament\Resources\UserActivityLogs\Tables\UserActivityLogsTable;
use App\Models\UserActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserActivityLogResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = UserActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['admin', 'manager'];

    protected static ?string $recordTitleAttribute = 'activity_type';

    public static function form(Schema $schema): Schema
    {
        return UserActivityLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserActivityLogsTable::configure($table);
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
            'index' => ListUserActivityLogs::route('/'),
            'create' => CreateUserActivityLog::route('/create'),
            'edit' => EditUserActivityLog::route('/{record}/edit'),
        ];
    }
}
