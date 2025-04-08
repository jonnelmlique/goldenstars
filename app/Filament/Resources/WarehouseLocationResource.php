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
            Forms\Components\Select::make('building_id')
                ->relationship('building', 'name')
                ->required()
                ->preload()
                ->searchable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shelves_count')
                    ->counts('shelves')
                    ->label('Shelves')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Building')
                    ->searchable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Location')
                        ->slideOver()
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Delete Location')
                        ->slideOver()
                        ->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label('Actions')
                    ->tooltip('Actions')
                    ->dropdownPlacement('bottom-end'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Create Location')
                    ->slideOver()
                    ->icon(icon: 'heroicon-m-plus'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Delete Selected Location')
                        ->slideOver(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\WarehouseLocationResource\Pages\ListWarehouseLocations::route('/'),
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
