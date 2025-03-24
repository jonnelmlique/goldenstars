<?php

namespace App\Filament\Resources;

use App\Models\WarehouseInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseInventoryResource extends Resource
{
    protected static ?string $model = WarehouseInventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Warehouse';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('shelf_id')
                ->relationship('shelf', 'name')
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\TextInput::make('sku')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('unit')
                    ->required(),
            ]),
            Forms\Components\TextInput::make('shelf_position')
                ->numeric()
                ->default(1)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('sku')
                ->searchable(),
            Tables\Columns\TextColumn::make('shelf.location.name')
                ->label('Location')
                ->searchable(),
            Tables\Columns\TextColumn::make('shelf.name')
                ->label('Shelf')
                ->searchable(),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('unit'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('shelf')
                    ->relationship('shelf', 'name'),
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('shelf.location', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\WarehouseInventoryResource\Pages\ListWarehouseInventory::route('/'),
            'create' => \App\Filament\Resources\WarehouseInventoryResource\Pages\CreateWarehouseInventory::route('/create'),
            'edit' => \App\Filament\Resources\WarehouseInventoryResource\Pages\EditWarehouseInventory::route('/{record}/edit'),
        ];
    }
}
