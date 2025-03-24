<?php

namespace App\Filament\Resources;

use App\Models\WarehouseShelf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseShelfResource extends Resource
{
    protected static ?string $model = WarehouseShelf::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Warehouse';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('location_id')
                ->relationship('location', 'name')
                ->required(),
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('level')
                ->numeric()
                ->required()
                ->default(1),
            Forms\Components\TextInput::make('capacity')
                ->numeric()
                ->required()
                ->default(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('location.name')
                ->searchable(),
            Tables\Columns\TextColumn::make('name')
                ->searchable(),
            Tables\Columns\TextColumn::make('code')
                ->searchable(),
            Tables\Columns\TextColumn::make('level'),
            Tables\Columns\TextColumn::make('capacity'),
            Tables\Columns\TextColumn::make('items_count')
                ->counts('items')
                ->label('Items'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\WarehouseShelfResource\Pages\ListWarehouseShelves::route('/'),
            'create' => \App\Filament\Resources\WarehouseShelfResource\Pages\CreateWarehouseShelf::route('/create'),
            'edit' => \App\Filament\Resources\WarehouseShelfResource\Pages\EditWarehouseShelf::route('/{record}/edit'),
        ];
    }
}
