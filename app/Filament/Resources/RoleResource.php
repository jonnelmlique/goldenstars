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
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
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
