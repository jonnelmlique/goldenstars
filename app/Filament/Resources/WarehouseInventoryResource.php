<?php

namespace App\Filament\Resources;

use App\Models\WarehouseInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\WarehouseInventoryResource\RelationManagers;
use Illuminate\Contracts\View\View;

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
                    Tables\Actions\Action::make('printBarcode')
                        ->label('Print Barcode')
                        ->icon('heroicon-m-document-text')
                        ->modalContent(function (WarehouseInventory $record): View {
                            return view('modals.barcode-print', [
                                'inventory' => $record
                            ]);
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Delete Inventory')
                        ->slideOver()
                        ->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Create Inventory')
                    ->slideOver()
                    ->icon('heroicon-m-plus'),
                Tables\Actions\Action::make('editByItemNumber')
                    ->label('Scan Barcode')
                    ->icon('heroicon-m-qr-code')
                    ->modalHeading('Edit Inventory')
                    ->modalWidth('4xl')
                    ->form([
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Find Inventory')
                                ->schema([
                                    Forms\Components\TextInput::make('item_number')
                                        ->required()
                                        ->label('Item Number')
                                        ->placeholder('Scan barcode or enter item number')
                                        ->helperText('Connect a barcode scanner to quickly scan item barcodes')
                                        ->autofocus()
                                        ->autocomplete(false)
                                        ->extraInputAttributes([
                                            'class' => 'barcode-scanner-input',
                                            'data-scanner-enabled' => 'true',
                                        ])
                                        ->live(debounce: 200) // Reduced debounce time for barcode scanners which typically complete quickly
                                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                                            $inventory = WarehouseInventory::where('item_number', $state)->first();

                                            if (!$inventory) {
                                                $set('inventory_id', null);
                                                $set('item_name', null);
                                                $set('batch_number', null);
                                                $set('location_code', null);
                                                $set('bom_unit', null);
                                                $set('physical_inventory', null);
                                                $set('physical_reserved', null);
                                                $set('actual_count', null);
                                                return;
                                            }

                                            $set('inventory_id', $inventory->id);
                                            $set('item_name', $inventory->item_name);
                                            $set('batch_number', $inventory->batch_number);
                                            $set('location_code', $inventory->location_code);
                                            $set('bom_unit', $inventory->bom_unit);
                                            $set('physical_inventory', $inventory->physical_inventory);
                                            $set('physical_reserved', $inventory->physical_reserved);
                                            $set('actual_count', $inventory->actual_count);
                                        }),
                                ]),

                            Forms\Components\Hidden::make('inventory_id'),

                            Forms\Components\Section::make('Inventory Details')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('item_name')
                                                ->required()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null),
                                            Forms\Components\TextInput::make('batch_number')
                                                ->required()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null),
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
                                                ->searchable()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null),
                                            Forms\Components\TextInput::make('bom_unit')
                                                ->label('BOM Unit')
                                                ->required()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null),
                                            Forms\Components\TextInput::make('physical_inventory')
                                                ->numeric()
                                                ->required()
                                                ->live()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null)
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    $reserved = intval($get('physical_reserved') ?? 0);
                                                    $inventory = intval($state ?? 0);
                                                    $set('actual_count', $inventory - $reserved);
                                                }),
                                            Forms\Components\TextInput::make('physical_reserved')
                                                ->numeric()
                                                ->required()
                                                ->live()
                                                ->disabled(fn(Forms\Get $get): bool => $get('inventory_id') === null)
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    $inventory = intval($get('physical_inventory') ?? 0);
                                                    $reserved = intval($state ?? 0);
                                                    $set('actual_count', $inventory - $reserved);
                                                }),
                                            Forms\Components\TextInput::make('actual_count')
                                                ->numeric()
                                                ->required()
                                                ->disabled()
                                                ->columnSpan(2),
                                        ]),
                                ])
                                ->visible(fn(Forms\Get $get): bool => $get('inventory_id') !== null),
                        ]),
                    ])
                    ->action(function (array $data) {
                        if (empty($data['inventory_id'])) {
                            Notification::make()
                                ->danger()
                                ->title('Update Failed')
                                ->body('No inventory record found to update.')
                                ->send();
                            return;
                        }

                        $inventory = WarehouseInventory::find($data['inventory_id']);

                        if (!$inventory) {
                            Notification::make()
                                ->danger()
                                ->title('Update Failed')
                                ->body('The inventory record could not be found.')
                                ->send();
                            return;
                        }

                        $inventory->update([
                            'item_name' => $data['item_name'],
                            'batch_number' => $data['batch_number'],
                            'location_code' => $data['location_code'],
                            'bom_unit' => $data['bom_unit'],
                            'physical_inventory' => $data['physical_inventory'],
                            'physical_reserved' => $data['physical_reserved'],
                            'actual_count' => $data['physical_inventory'] - $data['physical_reserved'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Inventory Updated')
                            ->body('The inventory has been successfully updated.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->modalHeading('Delete Selected Inventory')
                    ->slideOver(),
                Tables\Actions\BulkAction::make('printBarcodes')
                    ->label('Print Barcodes')
                    ->icon('heroicon-m-printer')
                    ->modalContent(function (Collection $records): View {
                        return view('modals.barcode-print-multiple', [
                            'inventories' => $records
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->deselectRecordsAfterCompletion(),
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
            'edit' => \App\Filament\Resources\WarehouseInventoryResource\Pages\EditWarehouseInventory::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\WarehouseInventoryResource\RelationManagers\TransfersRelationManager::class,
        ];
    }
}
