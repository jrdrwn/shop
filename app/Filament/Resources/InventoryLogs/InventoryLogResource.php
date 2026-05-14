<?php

namespace App\Filament\Resources\InventoryLogs;

use App\Filament\Concerns\ResolvesSubscription;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\InventoryLogs\Pages\ListInventoryLogs;
use App\Filament\Resources\InventoryLogs\Schemas\InventoryLogForm;
use App\Filament\Resources\InventoryLogs\Tables\InventoryLogsTable;
use App\Models\InventoryLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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

    protected static array $allowedRoles = ['owner', 'gudang'];

    protected static ?string $recordTitleAttribute = 'action';

    /**
     * Gate access: Owner must have an active subscription with can_use_inventory = true.
     * Super admin always has access.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        $role = static::normalizeRole($user?->role);

        if (! in_array($role, ['owner', 'gudang'], true)) {
            return false;
        }

        $toko = static::tokoForCurrentUser();

        if (! $toko) {
            return true;
        }

        return static::subscriptionService()->canUseInventory($toko);
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

        if (in_array($user?->role, ['owner', 'gudang']) && filled($user->toko_id)) {
            return $query->where('toko_id', $user->toko_id);
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
