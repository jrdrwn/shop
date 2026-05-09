<?php

namespace App\Filament\Resources\Cafes;

use App\Filament\Resources\Cafes\Infolists\CafeInfolist;
use App\Filament\Resources\Cafes\Pages\EditCafe;
use App\Filament\Resources\Cafes\Pages\ListCafes;
use App\Filament\Resources\Cafes\Pages\ViewCafe;
use App\Filament\Resources\Cafes\Schemas\CafeForm;
use App\Filament\Resources\Cafes\Tables\CafesTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\Cafe;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CafeResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = Cafe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Cafes & Managers';

    protected static ?string $roleNavigationGroup = 'Platform';

    /**
     * super_admin : read-only (list + view)
     * manager     : edit their own cafe only
     */
    protected static array $allowedRoles = ['super_admin', 'manager'];

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CafeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CafeInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['manager.manager', 'subscription']);

        // Manager can only see their own cafe
        if (Auth::user()?->role === 'manager') {
            $query->where('id', Auth::user()?->cafe_id);
        }

        return $query;
    }

    /** Nobody creates cafes from the panel */
    public static function canCreate(): bool
    {
        return false;
    }

    /** Only manager can edit — and only their own cafe */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user?->role === 'manager' && (int) $user->cafe_id === (int) $record->id;
    }

    /** Nobody deletes cafes from the panel */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();
        $items = [
            NavigationItem::make('Detail')
                ->icon(Heroicon::OutlinedEye)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === ViewCafe::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
        ];

        // Only show Edit tab if manager can edit this cafe
        if (static::canEdit($record)) {
            $items[] = NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === EditCafe::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record]));
        }

        return $items;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafes::route('/'),
            'view' => ViewCafe::route('/{record}'),
            'edit' => EditCafe::route('/{record}/edit'),
        ];
    }
}
