<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn($component, $get) => $component->getRecord() === null)
                    ->minLength(8)
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn($state) => filled($state)),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'code')
                    ->required(),
                Forms\Components\Select::make('building_id')
                    ->relationship('building', 'code')
                    ->required(),
                Forms\Components\Select::make('role_id')
                    ->relationship('role', 'code')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('department.name'),
                Tables\Columns\TextColumn::make('building.name'),
                Tables\Columns\TextColumn::make('role.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('users.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('users.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('users.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('users.delete');
    }
}
