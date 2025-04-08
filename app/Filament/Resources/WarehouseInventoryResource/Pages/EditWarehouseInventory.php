<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use App\Models\WarehouseShelf;
use App\Models\WarehouseTransfer;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Filament\Support\Enums\Alignment;

class EditWarehouseInventory extends EditRecord
{
    protected static string $resource = WarehouseInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printBarcode')
                ->label('Print Barcode')
                ->icon('heroicon-o-printer')
                ->modalContent(function (): View {
                    return view('modals.barcode-print', [
                        'inventory' => $this->record
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            Actions\Action::make('receive')
                ->label('Receive Transfer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->hasPendingTransfer())
                ->requiresConfirmation()
                ->action(function (): void {
                    $transfer = $this->record->warehouseTransfers()
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

                    $this->record->update([
                        'location_code' => $transfer->to_location
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Transfer Completed')
                        ->body('Items have been received at the new location.')
                        ->send();

                    // Refresh the record and form without redirecting
                    $this->record = $this->getRecord();
                    $this->fillForm();
                }),
            Actions\Action::make('transfer')
                ->label('Transfer Item')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->visible(fn() => !$this->record->hasPendingTransfer())
                ->form([
                    Forms\Components\Select::make('to_location')
                        ->label('Transfer to Location')
                        ->options(function () {
                            $shelves = WarehouseShelf::with(['location.building'])->get();
                            return $shelves->mapWithKeys(function ($shelf) {
                                $buildingName = $shelf->location->building->name ?? 'Unknown';
                                return [$shelf->location_code => "{$shelf->location_code} ({$buildingName})"];
                            })->toArray();
                        })
                        ->required()
                        ->searchable(),
                    Forms\Components\DateTimePicker::make('transfer_date')
                        ->label('Transfer Date & Time')
                        ->required()
                        ->seconds(false)
                        ->default(now()),
                    Forms\Components\Textarea::make('notes')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    if ($this->record->actual_count <= 0) {
                        Notification::make()
                            ->danger()
                            ->title('Transfer Failed')
                            ->body('Cannot transfer items with zero or negative actual count.')
                            ->send();
                        return;
                    }

                    WarehouseTransfer::create([
                        'inventory_id' => $this->record->id,
                        'from_location' => $this->record->location_code,
                        'to_location' => $data['to_location'],
                        'quantity' => $this->record->actual_count,
                        'transfer_date' => $data['transfer_date'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'pending',
                        'received_date' => null,
                    ]);

                    // Show notification
                    Notification::make()
                        ->success()
                        ->title('Transfer Initiated')
                        ->body('Transfer has been created and is pending reception.')
                        ->send();

                    // Refresh the form data and record without redirecting
                    $this->record = $this->getRecord();
                    $this->fillForm();

                    // Close the modal
                    $this->closeActionModal();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\ViewField::make('barcode')
                            ->view('filament.forms.components.inventory-barcode-display')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Wizard::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Wizard\Step::make('Basic Information')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('item_number')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('item_name')
                                    ->required()
                                    ->autocapitalize('words')
                                    ->placeholder('Enter item name')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('batch_number')
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('bom_unit')
                                    ->label('BOM Unit')
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                        Forms\Components\Wizard\Step::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\TextInput::make('location_code')
                                    ->label('Location*')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixIcon('heroicon-m-lock-closed')
                                    ->helperText('Location can only be changed using the Transfer function')
                                    ->columnSpanFull(),
                                Forms\Components\Placeholder::make('location_info')
                                    ->content(function (Forms\Get $get) {
                                        $locationCode = $get('location_code');
                                        if (!$locationCode)
                                            return 'Select a location to see details';

                                        $shelf = WarehouseShelf::where('location_code', $locationCode)->first();
                                        if (!$shelf)
                                            return 'Location information not found';

                                        $buildingName = $shelf->location?->building?->name ?? 'Unknown Building';
                                        $locationName = $shelf->location?->name ?? 'Unknown Location';

                                        return "Building: {$buildingName} | Location: {$locationName} | Shelf: {$shelf->name} | Level: {$shelf->level}";
                                    })
                                    ->columnSpanFull(),
                                Forms\Components\Placeholder::make('transfer_info')
                                    ->content('To move this item to a different location, use the "Transfer Item" button in the top right of this page.')
                                    ->extraAttributes(['class' => 'text-sm text-warning-600 dark:text-warning-400 font-medium'])
                                    ->visible(fn() => !$this->record->hasPendingTransfer())
                                    ->columnSpanFull(),
                                Forms\Components\Placeholder::make('pending_transfer')
                                    ->content('This item has a pending transfer and cannot be modified until the transfer is completed or canceled.')
                                    ->extraAttributes(['class' => 'text-sm text-danger-600 dark:text-danger-400 font-medium'])
                                    ->visible(fn() => $this->record->hasPendingTransfer())
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Wizard\Step::make('Inventory Counts')
                            ->icon('heroicon-o-calculator')
                            ->schema([
                                Forms\Components\TextInput::make('physical_inventory')
                                    ->numeric()
                                    ->required()
                                    ->step(1)
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $reserved = intval($get('physical_reserved') ?? 0);
                                        $inventory = intval($state ?? 0);
                                        $set('actual_count', $inventory - $reserved);
                                    })
                                    ->suffixIcon('heroicon-m-cube')
                                    ->helperText('Total quantity in stock'),
                                Forms\Components\TextInput::make('physical_reserved')
                                    ->numeric()
                                    ->required()
                                    ->step(1)
                                    ->minValue(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $inventory = intval($get('physical_inventory') ?? 0);
                                        $reserved = intval($state ?? 0);
                                        $set('actual_count', $inventory - $reserved);
                                    })
                                    ->suffixIcon('heroicon-m-lock-closed')
                                    ->helperText('Amount reserved for orders'),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('actual_count')
                                            ->numeric()
                                            ->disabled()
                                            ->suffixIcon(function (Forms\Get $get) {
                                                $count = intval($get('actual_count') ?? 0);
                                                return $count > 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle';
                                            })
                                            ->extraAttributes(function (Forms\Get $get) {
                                                $count = intval($get('actual_count') ?? 0);
                                                return [
                                                    'class' => $count <= 0 ? 'fi-input-danger' : 'fi-input-success',
                                                ];
                                            }),
                                        Forms\Components\Placeholder::make('status_indicator')
                                            ->content(function (Forms\Get $get) {
                                                $count = intval($get('actual_count') ?? 0);
                                                if ($count <= 0) {
                                                    return 'No available inventory';
                                                } elseif ($count < 10) {
                                                    return 'Low inventory';
                                                } else {
                                                    return 'Sufficient inventory';
                                                }
                                            })
                                            ->extraAttributes(function (Forms\Get $get) {
                                                $count = intval($get('actual_count') ?? 0);
                                                $class = 'px-4 py-2 rounded-lg text-white text-center';
                                                if ($count <= 0) {
                                                    return ['class' => $class . ' bg-danger-500'];
                                                } elseif ($count < 10) {
                                                    return ['class' => $class . ' bg-warning-500'];
                                                } else {
                                                    return ['class' => $class . ' bg-success-500'];
                                                }
                                            }),
                                    ]),
                            ]),
                    ]),
                Forms\Components\Section::make('Recent Activity')
                    ->schema([
                        Forms\Components\ViewField::make('recent_transfers')
                            ->view('filament.forms.components.inventory-recent-transfers')
                            ->visible(fn($record) => $record && $record->warehouseTransfers()->exists())
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getRedirectUrl(): ?string
    {
        // Return null to stay on the same page after saving
        return null;
    }

    // Override the afterSave method to prevent any redirection
    protected function afterSave(): void
    {
        // Show a notification instead of redirecting
        Notification::make()
            ->success()
            ->title('Inventory updated')
            ->body('The inventory has been updated successfully.')
            ->send();
    }

    // Remove or override the getSavedNotification method since we're handling notifications in afterSave
    protected function getSavedNotification(): ?Notification
    {
        // Return null since we're sending notification in afterSave
        return null;
    }
}
