<?php

namespace App\Filament\Resources\CafeManagers;

use App\Filament\Resources\CafeManagers\Infolists\CafeManagerInfolist;
use App\Filament\Resources\CafeManagers\Pages\CreateCafeManager;
use App\Filament\Resources\CafeManagers\Pages\EditCafeManager;
use App\Filament\Resources\CafeManagers\Pages\ListCafeManagers;
use App\Filament\Resources\CafeManagers\Pages\ViewCafeManager;
use App\Filament\Resources\CafeManagers\Schemas\CafeManagerForm;
use App\Filament\Resources\CafeManagers\Tables\CafeManagersTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\CafeManager;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CafeManagerResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = CafeManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'manager_id';

    protected static ?string $roleNavigationGroup = 'Manajemen Pengguna';

    protected static array $allowedRoles = ['super_admin'];

    /**
     * @deprecated Fully replaced by Cafes table/infolist showing manager info inline.
     *             No longer accessible from the panel.
     */
    public static function canAccess(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return CafeManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafeManagersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CafeManagerInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['cafe.subscription', 'manager', 'assignedBy']);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        return [
            NavigationItem::make('View')
                ->icon(Heroicon::OutlinedEye)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === ViewCafeManager::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
            NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === EditCafeManager::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafeManagers::route('/'),
            'view' => ViewCafeManager::route('/{record}'),
            'create' => CreateCafeManager::route('/create'),
            'edit' => EditCafeManager::route('/{record}/edit'),
        ];
    }
}
