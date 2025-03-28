<?php

namespace App\Filament\Resources;

use App\Models\WarehouseShelf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WarehouseShelfResource extends Resource
{
    protected static ?string $model = WarehouseShelf::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 4;

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
            Forms\Components\TextInput::make('location_code')
                ->label('Location Code')
                ->required()
                ->placeholder('e.g. LOCATION A0171')
                ->helperText('Specific location code for this shelf')
                ->maxLength(20),
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
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location_code')
                    ->label('Location Code')
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

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('warehouse.shelves.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('warehouse.shelves.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.shelves.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.shelves.delete');
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
