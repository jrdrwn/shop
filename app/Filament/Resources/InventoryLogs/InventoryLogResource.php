<?php

namespace App\Filament\Resources\InventoryLogs;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\InventoryLogs\Pages\CreateInventoryLog;
use App\Filament\Resources\InventoryLogs\Pages\EditInventoryLog;
use App\Filament\Resources\InventoryLogs\Pages\ListInventoryLogs;
use App\Filament\Resources\InventoryLogs\Schemas\InventoryLogForm;
use App\Filament\Resources\InventoryLogs\Tables\InventoryLogsTable;
use App\Models\InventoryLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InventoryLogResource extends Resource
{
    use HasRoleNavigation, ResolvesSubscription;

    protected static ?string $model = InventoryLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Inventaris';

    protected static ?string $pluralModelLabel = 'Log Inventaris';

    protected static ?string $modelLabel = 'Log Inventaris';

    protected static ?string $roleNavigationGroup = 'Laporan';

    protected static array $allowedRoles = ['manager'];

    protected static ?string $recordTitleAttribute = 'action';

    /**
     * Gate access: manager must have an active subscription with can_use_inventory = true.
     * Super admin always has access.
     */
    public static function canAccess(): bool
    {
        $cafe = static::cafeForCurrentUser();

        if (!$cafe) {
            return false;
        }

        return static::subscriptionService()->canUseInventory($cafe);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return InventoryLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryLogsTable::configure($table);
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
            'index' => ListInventoryLogs::route('/'),
        ];
    }
}
