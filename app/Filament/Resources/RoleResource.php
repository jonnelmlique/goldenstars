<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'IT';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        $getPermissionsByGroup = function ($group) {
            return \App\Models\Permission::where('group', $group)
                ->pluck('name', 'id')
                ->toArray();
        };

        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
            Forms\Components\Tabs::make('Permissions')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Users')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Users'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Departments')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Departments'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Buildings')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Buildings'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Roles')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Roles'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Tickets')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Tickets'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Ticket Categories')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Ticket Categories'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Inventory')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Inventory'))
                                ->columns(2)
                                ->gridDirection('row'),
                        ]),
                    Forms\Components\Tabs\Tab::make('Warehouse')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->relationship('permissions', 'name')
                                ->options($getPermissionsByGroup('Warehouse'))
                                ->columns(2)
                                ->gridDirection('row')
                                ->descriptions([
                                    'warehouse.view' => 'Can view warehouse 3D visualization',
                                    'warehouse.inventory.view' => 'Can view warehouse inventory',
                                    'warehouse.inventory.create' => 'Can create warehouse inventory',
                                    'warehouse.inventory.edit' => 'Can edit warehouse inventory',
                                    'warehouse.inventory.delete' => 'Can delete warehouse inventory',
                                    'warehouse.locations.view' => 'Can view warehouse locations',
                                    'warehouse.locations.create' => 'Can create warehouse locations',
                                    'warehouse.locations.edit' => 'Can edit warehouse locations',
                                    'warehouse.locations.delete' => 'Can delete warehouse locations',
                                    'warehouse.shelves.view' => 'Can view warehouse shelves',
                                    'warehouse.shelves.create' => 'Can create warehouse shelves',
                                    'warehouse.shelves.edit' => 'Can edit warehouse shelves',
                                    'warehouse.shelves.delete' => 'Can delete warehouse shelves',
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i A')
                    ->timezone('Asia/Manila'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('roles.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('roles.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('roles.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('roles.delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
        ];
    }
}
