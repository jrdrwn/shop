<?php

namespace App\Filament\Resources\TokoOwners;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\TokoOwners\Infolists\TokoOwnerInfolist;
use App\Filament\Resources\TokoOwners\Pages\CreateTokoOwner;
use App\Filament\Resources\TokoOwners\Pages\EditTokoOwner;
use App\Filament\Resources\TokoOwners\Pages\ListTokoOwners;
use App\Filament\Resources\TokoOwners\Pages\ViewTokoOwner;
use App\Filament\Resources\TokoOwners\Schemas\TokoOwnerForm;
use App\Filament\Resources\TokoOwners\Tables\TokoOwnersTable;
use App\Models\TokoOwner;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TokoOwnerResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = TokoOwner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'owner_id';

    protected static ?string $roleNavigationGroup = 'Manajemen Pengguna';

    protected static array $allowedRoles = ['super_admin'];

    /**
     * @deprecated Fully replaced by Tokos table/infolist showing owner info inline.
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
        return TokoOwnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TokoOwnersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TokoOwnerInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['toko.subscription', 'owner', 'assignedBy']);
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
                ->isActiveWhen(fn (): bool => $page::getRouteName() === ViewTokoOwner::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
            NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === EditTokoOwner::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTokoOwners::route('/'),
            'view' => ViewTokoOwner::route('/{record}'),
            'create' => CreateTokoOwner::route('/create'),
            'edit' => EditTokoOwner::route('/{record}/edit'),
        ];
    }
}
