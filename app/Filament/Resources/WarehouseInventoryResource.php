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
                    $shelves = \App\Models\WarehouseShelf::with(['location.building'])->get();
                    return $shelves->mapWithKeys(function ($shelf) {
                        $buildingName = $shelf->location?->building?->name ?? 'Unknown';
                        return [$shelf->location_code => "{$shelf->location_code} - {$buildingName}"];
                    })->toArray();
                })
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('bom_unit')
                ->label('BOM Unit')
                ->required(),
            Forms\Components\TextInput::make('physical_inventory')
                ->numeric()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $reserved = intval($get('physical_reserved') ?? 0);
                    $inventory = intval($state ?? 0);
                    $set('actual_count', $inventory - $reserved);
                }),
            Forms\Components\TextInput::make('physical_reserved')
                ->numeric()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $inventory = intval($get('physical_inventory') ?? 0);
                    $reserved = intval($state ?? 0);
                    $set('actual_count', $inventory - $reserved);
                }),
            Forms\Components\TextInput::make('actual_count')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn($query) => $query->with(['shelf.location.building']))
            ->columns([
                Tables\Columns\TextColumn::make('item_number')
                    ->label('Item No.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-identification')
                    ->color('primary')
                    ->weight('bold')
                    ->alignCenter()

                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('batch_number')
                    ->icon('heroicon-m-hashtag')
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location_code')
                    ->label('Location')
                    ->formatStateUsing(function ($record) {
                        $buildingName = $record->shelf?->location?->building?->name ?? 'Unknown';
                        return view('filament.tables.columns.location-with-building', [
                            'location' => $record->location_code,
                            'building' => $buildingName
                        ]);
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('shelf.location.building', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })->orWhere('location_code', 'like', "%{$search}%");
                    })
                    ->icon('heroicon-m-map-pin')
                    ->alignCenter()

                    ->toggleable(),
                Tables\Columns\TextColumn::make('bom_unit')
                    ->label('BOM Unit')
                    ->badge()
                    ->alignCenter()
                    ->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('physical_inventory')
                    ->numeric()
                    ->badge()
                    ->alignCenter()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('physical_reserved')
                    ->numeric()
                    ->badge()
                    ->alignCenter()
                    ->color('warning')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('actual_count')
                    ->numeric()
                    ->badge()
                    ->alignCenter()
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building')
                    ->label('Building')
                    ->relationship('shelf.location.building', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('location_code')
                    ->label('Location')
                    ->options(function () {
                        return \App\Models\WarehouseShelf::with('location.building')
                            ->get()
                            ->mapWithKeys(function ($shelf) {
                                $buildingName = $shelf->location?->building?->name ?? 'Unknown';
                                return [$shelf->location_code => "{$shelf->location_code} - {$buildingName}"];
                            })
                            ->toArray();
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading('View Inventory')
                        ->url(fn(Model $record): string => static::getUrl('view', ['record' => $record]))
                        ->icon('heroicon-m-eye'),
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Edit Inventory')
                        ->slideOver()
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\Action::make('transfer')
                        ->icon('heroicon-m-arrow-path-rounded-square')
                        ->visible(fn(WarehouseInventory $record): bool => !$record->hasPendingTransfer())
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

                            // Create transfer history record with pending status
                            \App\Models\WarehouseTransfer::create([
                                'inventory_id' => $record->id,
                                'from_location' => $record->location_code,
                                'to_location' => $data['to_location'],
                                'quantity' => $record->actual_count,
                                'transfer_date' => $data['transfer_date'],
                                'notes' => $data['notes'] ?? null,
                                'status' => 'pending',
                                'received_date' => null,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Transfer Initiated')
                                ->body('Transfer has been created and is pending reception.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('receive')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn(WarehouseInventory $record): bool => $record->hasPendingTransfer())
                        ->requiresConfirmation()
                        ->action(function (WarehouseInventory $record): void {
                            $transfer = $record->warehouseTransfers()
                                ->where('status', 'pending')
                                ->first();

                            if (!$transfer) {
                                Notification::make()
                                    ->danger()
                                    ->title('Receive Failed')
                                    ->body('No pending transfer found.')
                                    ->send();
                                return;
                            }

                            $transfer->update([
                                'status' => 'completed',
                                'received_date' => now(),
                            ]);

                            $record->update([
                                'location_code' => $transfer->to_location
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Transfer Completed')
                                ->body('Items have been received at the new location.')
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Delete Inventory')
                        ->slideOver()
                        ->icon('heroicon-m-trash'),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
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
