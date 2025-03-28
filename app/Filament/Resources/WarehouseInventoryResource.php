<?php

namespace App\Filament\Resources;

use App\Models\WarehouseInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Illuminate\Database\Eloquent\Model;

class WarehouseInventoryResource extends Resource
{
    protected static ?string $model = WarehouseInventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';  // Changed to a different 3D box icon
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('item_number')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('item_name')
                ->required(),
            Forms\Components\TextInput::make('batch_number')
                ->required(),
            Forms\Components\Select::make('location_code')
                ->label('Location')
                ->options(function () {
                    return \App\Models\WarehouseShelf::pluck('location_code', 'location_code')
                        ->toArray();
                })
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('bom_unit')
                ->label('BOM Unit')
                ->required(),
            Forms\Components\TextInput::make('physical_inventory')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('physical_reserved')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('actual_count')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location_code')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bom_unit')
                    ->label('BOM Unit')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('physical_inventory')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('physical_reserved')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('actual_count')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_code')
                    ->options(function () {
                        return \App\Models\WarehouseShelf::pluck('location_code', 'location_code')
                            ->toArray();
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('warehouse.inventory.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('warehouse.inventory.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.inventory.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('warehouse.inventory.delete');
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
