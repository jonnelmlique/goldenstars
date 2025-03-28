<?php

namespace App\Filament\Resources;

use App\Models\WarehouseLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocationResource extends Resource
{
    protected static ?string $model = WarehouseLocation::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('x_position')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('y_position')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('z_position')
                    ->numeric()
                    ->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('shelves_count')
                ->counts('shelves')
                ->label('Shelves'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\WarehouseLocationResource\Pages\ListWarehouseLocations::route('/'),
            'create' => \App\Filament\Resources\WarehouseLocationResource\Pages\CreateWarehouseLocation::route('/create'),
            'edit' => \App\Filament\Resources\WarehouseLocationResource\Pages\EditWarehouseLocation::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('warehouse.locations.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('warehouse.locations.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.locations.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.locations.delete');
    }
}
