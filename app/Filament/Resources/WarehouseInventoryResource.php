<?php

namespace App\Filament\Resources;

use App\Models\WarehouseInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Notifications\Notification; // Add this import
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\WarehouseInventoryResource\RelationManagers;

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
            ->defaultSort('created_at', 'desc')
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
                Tables\Actions\ViewAction::make()
                    ->modalHeading('View Inventory')
                    ->url(fn(Model $record): string => static::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Inventory')
                    ->slideOver(),
                Tables\Actions\Action::make('transfer')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->form([
                        Forms\Components\Select::make('to_location')
                            ->label('Transfer to Location')
                            ->options(function () {
                                $shelves = \App\Models\WarehouseShelf::with(['location.building'])->get();
                                return $shelves->mapWithKeys(function ($shelf) {
                                    $buildingName = $shelf->location->building->name ?? 'Unknown';
                                    return [$shelf->location_code => "{$shelf->location_code} ({$buildingName})"];
                                })->toArray();
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\DatePicker::make('transfer_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record): void {
                        if ($record->actual_count <= 0) {
                            Notification::make()
                                ->danger()
                                ->title('Transfer Failed')
                                ->body('Cannot transfer items with zero or negative actual count.')
                                ->send();
                            return;
                        }

                        // Create transfer history record
                        \App\Models\WarehouseTransfer::create([
                            'inventory_id' => $record->id,
                            'from_location' => $record->location_code,
                            'to_location' => $data['to_location'],
                            'quantity' => $record->actual_count,
                            'transfer_date' => $data['transfer_date'],
                            'notes' => $data['notes'] ?? null,
                            'status' => 'completed',
                            'received_date' => now(),
                        ]);

                        // Update the location
                        $record->update([
                            'location_code' => $data['to_location']
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Transfer Successful')
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Delete Inventory')
                    ->slideOver(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Create Inventory')
                    ->slideOver()
                    ->icon('heroicon-m-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Delete Selected Inventory')
                        ->slideOver(),
                ]),
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
            'view' => \App\Filament\Resources\WarehouseInventoryResource\Pages\ViewWarehouseInventory::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\WarehouseInventoryResource\RelationManagers\TransfersRelationManager::class,
        ];
    }
}
