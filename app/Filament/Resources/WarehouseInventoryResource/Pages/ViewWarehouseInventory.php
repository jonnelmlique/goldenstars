<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\ImageEntry;

class ViewWarehouseInventory extends ViewRecord implements HasActions
{
    use InteractsWithActions;
    use InteractsWithFormActions;

    protected static string $resource = WarehouseInventoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('transfer')
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->visible(fn() => !$this->record->hasPendingTransfer())
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

                    \App\Models\WarehouseTransfer::create([
                        'inventory_id' => $this->record->id,
                        'from_location' => $this->record->location_code,
                        'to_location' => $data['to_location'],
                        'quantity' => $this->record->actual_count,
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

                    $this->getRecord(true);
                    $this->dispatch('refresh'); // Add dispatch refresh event
                }),

            Actions\Action::make('receive')
                ->icon('heroicon-m-check-circle')
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

                    $this->getRecord(true);
                    $this->dispatch('refresh'); // Add dispatch refresh event
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        // Get both barcode versions
        $lightBarcode = $this->record->getBarcode(2, 100, false);
        $darkBarcode = $this->record->getBarcode(2, 100, true);

        return $infolist
            ->schema([
                Infolists\Components\Grid::make(2)
                    ->schema([
                        Infolists\Components\Section::make('Item Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('item_number')
                                    ->label('Item Number')
                                    ->weight('bold')
                                    ->color('primary')
                                    ->size('lg'),
                                Infolists\Components\TextEntry::make('item_name')
                                    ->label('Item Name')
                                    ->size('lg'),
                                Infolists\Components\TextEntry::make('batch_number')
                                    ->label('Batch Number')
                                    ->icon('heroicon-m-hashtag'),
                                Infolists\Components\TextEntry::make('bom_unit')
                                    ->label('BOM Unit')
                                    ->badge()
                                    ->color('gray'),
                            ])->columnSpan(1),

                        Infolists\Components\Section::make('Barcode')
                            ->schema([
                                Infolists\Components\TextEntry::make('barcode')
                                    ->label('Item Barcode')
                                    ->html()
                                    ->state(function ($record) {
                                        try {
                                            $barcodeImage = $record->getBarcodeImage(2, 100);
                                            return "
                                                <div style='background-color: white; display: inline-block; padding: 10px; border-radius: 4px; margin: 0 auto;'>
                                                    <img src='{$barcodeImage}' alt='{$record->item_number}' style='max-width: 100%;'>
                                                </div>
                                            ";
                                        } catch (\Exception $e) {
                                            return "<div>Error generating barcode: {$e->getMessage()}</div>";
                                        }
                                    })
                                    ->alignment('center'),
                                Infolists\Components\TextEntry::make('item_number')
                                    ->label(false)
                                    ->alignment('center')
                                    ->size('sm')
                                    ->fontFamily('mono'),
                            ])->columnSpan(1),
                    ]),

                Infolists\Components\Section::make('Location Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('location_code')
                            ->label('Location Code')
                            ->icon('heroicon-m-map-pin')
                            ->size('lg')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('building_name')
                            ->label('Building')
                            ->getStateUsing(fn() => $this->record->shelf?->location?->building?->name ?? 'Unknown Building')
                            ->icon('heroicon-m-building-office'),
                    ])->columns(2),

                Infolists\Components\Section::make('Inventory Status')
                    ->compact()
                    ->schema([
                        Infolists\Components\Group::make([
                            Infolists\Components\TextEntry::make('physical_inventory')
                                ->label('Physical Inventory')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-m-cube')
                                ->size('lg'),
                            Infolists\Components\TextEntry::make('physical_reserved')
                                ->label('Reserved')
                                ->badge()
                                ->color('warning')
                                ->icon('heroicon-m-lock-closed')
                                ->size('lg'),
                            Infolists\Components\TextEntry::make('actual_count')
                                ->label('Actual Count')
                                ->badge()
                                ->color('success')
                                ->icon('heroicon-m-check-circle')
                                ->size('lg'),
                        ])->columns(3),
                    ]),

                Infolists\Components\Section::make('Transfer Information')
                    ->hidden(fn() => !$this->record->hasPendingTransfer())
                    ->schema([
                        Infolists\Components\TextEntry::make('transfer_status')
                            ->label('Status')
                            ->getStateUsing('Pending Transfer')
                            ->badge()
                            ->color('danger')
                            ->icon('heroicon-m-arrow-path-rounded-square')
                            ->size('lg'),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('transfer_from')
                                    ->label('From Location')
                                    ->getStateUsing(function () {
                                        $transfer = $this->record->warehouseTransfers()->where('status', 'pending')->first();
                                        if (!$transfer)
                                            return '-';

                                        $building = $transfer->fromShelf?->location?->building?->name ?? 'Unknown Building';
                                        return $transfer->from_location . ' (' . $building . ')';
                                    }),
                                Infolists\Components\TextEntry::make('transfer_to')
                                    ->label('To Location')
                                    ->getStateUsing(function () {
                                        $transfer = $this->record->warehouseTransfers()->where('status', 'pending')->first();
                                        if (!$transfer)
                                            return '-';

                                        $building = $transfer->toShelf?->location?->building?->name ?? 'Unknown Building';
                                        return $transfer->to_location . ' (' . $building . ')';
                                    }),
                                Infolists\Components\TextEntry::make('transfer_date')
                                    ->label('Transfer Date & Time')
                                    ->getStateUsing(function () {
                                        $transfer = $this->record->warehouseTransfers()->where('status', 'pending')->first();
                                        if (!$transfer || !$transfer->transfer_date)
                                            return '-';

                                        return $transfer->transfer_date->format('M d, Y h:i A');
                                    }),
                                Infolists\Components\TextEntry::make('transfer_notes')
                                    ->label('Notes')
                                    ->getStateUsing(function () {
                                        $transfer = $this->record->warehouseTransfers()->where('status', 'pending')->first();
                                        return $transfer?->notes ?? 'No notes';
                                    }),
                            ]),
                    ]),
            ]);
    }
}
