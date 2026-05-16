<?php

namespace App\Filament\Resources\Tokos;

use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Filament\Resources\Tokos\Infolists\TokoInfolist;
use App\Filament\Resources\Tokos\Pages\EditToko;
use App\Filament\Resources\Tokos\Pages\ListTokos;
use App\Filament\Resources\Tokos\Pages\ViewToko;
use App\Filament\Resources\Tokos\Schemas\TokoForm;
use App\Filament\Resources\Tokos\Tables\TokosTable;
use App\Models\Toko;
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

class TokoResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = Toko::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    public static function getNavigationLabel(): string
    {
        if (auth()->user()?->role === 'super_admin') {
            return 'Toko';
        }

        return 'Toko & Owner';
    }

    protected static ?string $pluralModelLabel = 'Toko';

    protected static ?string $modelLabel = 'Toko';

    // protected static ?string $roleNavigationGroup = 'Platform';

    // public static function getNavigationGroup(): ?string
    // {
    //     if (Auth::user()?->role === 'super_admin') {
    //         return null;
    //     }

    //     return 'Toko & Owner';
    // }

    /**
     * super_admin : read-only (list + view)
     * Owner     : edit their own toko only
     */
    protected static array $allowedRoles = ['super_admin', 'owner'];

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TokoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TokosTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TokoInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['owner', 'subscription']);

        // Owner can only see their own store
        if (Auth::user()?->role === 'owner') {
            $query->where('id', Auth::user()?->toko_id);
        }

        return $query;
    }

    /** Nobody creates stores from the panel */
    public static function canCreate(): bool
    {
        return false;
    }

    /** Only Owner can edit — and only their own store */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user?->role === 'owner' && (int) $user->toko_id === (int) $record->id;
    }

    /** Nobody deletes tokos from the panel */
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
                ->isActiveWhen(fn(): bool => $page::getRouteName() === ViewToko::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
        ];

        // Only show Edit tab if Owner can edit this toko
        if (static::canEdit($record)) {
            $items[] = NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn(): bool => $page::getRouteName() === EditToko::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record]));
        }

        return $items;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTokos::route('/'),
            'view' => ViewToko::route('/{record}'),
            'edit' => EditToko::route('/{record}/edit'),
        ];
    }
}
