<?php

namespace App\Filament\Resources\WarehouseInventoryResource\Pages;

use App\Filament\Resources\WarehouseInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;

class ListWarehouseInventory extends ListRecords
{
    protected static string $resource = WarehouseInventoryResource::class;

    public $date_from = null;
    public $date_to = null;
    public $location = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Generate Report')
                ->icon('heroicon-o-document-arrow-down')
                ->form([
                    \Filament\Forms\Components\Grid::make(3)
                        ->schema([
                            \Filament\Forms\Components\DatePicker::make('date_from')
                                ->label('From Date')
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->date_from = $state;
                                }),
                            \Filament\Forms\Components\DatePicker::make('date_to')
                                ->label('To Date')
                                ->default(now())
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->date_to = $state;
                                }),
                            \Filament\Forms\Components\Select::make('location')
                                ->label('Location')
                                ->options(function () {
                                    return \App\Models\WarehouseShelf::pluck('location_code', 'location_code')
                                        ->toArray();
                                })
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->location = $state;
                                })
                                ->preload()
                                ->searchable(),
                        ])
                ])
                ->slideOver()
                ->modalWidth('7xl')
                ->action(function () {
                    $inventories = \App\Models\WarehouseInventory::query()
                        ->when($this->location, fn($query) => $query->where('location_code', $this->location))
                        ->when($this->date_from, fn($query) => $query->whereDate('created_at', '>=', $this->date_from))
                        ->when($this->date_to, fn($query) => $query->whereDate('created_at', '<=', $this->date_to))
                        ->get();

                    $pdf = Pdf::loadView('pdf.warehouse-inventory', [
                        'inventories' => $inventories,
                        'date' => now()->format('M d, Y'),
                        'date_from' => $this->date_from ? date('M d, Y', strtotime($this->date_from)) : null,
                        'date_to' => $this->date_to ? date('M d, Y', strtotime($this->date_to)) : null,
                        'data' => [
                            'location' => $this->location,
                            'date_from' => $this->date_from,
                            'date_to' => $this->date_to,
                        ],
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'warehouse-inventory.pdf');
                })
                ->modalContent(function (): View {
                    $inventories = \App\Models\WarehouseInventory::query()
                        ->when($this->location, fn($query) => $query->where('location_code', $this->location))
                        ->when($this->date_from, fn($query) => $query->whereDate('created_at', '>=', $this->date_from))
                        ->when($this->date_to, fn($query) => $query->whereDate('created_at', '<=', $this->date_to))
                        ->get();

                    return view('pdf.warehouse-inventory-preview', [
                        'inventories' => $inventories,
                        'date' => now()->format('M d, Y'),
                        'date_from' => $this->date_from ? date('M d, Y', strtotime($this->date_from)) : null,
                        'date_to' => $this->date_to ? date('M d, Y', strtotime($this->date_to)) : null,
                        'data' => [
                            'location' => $this->location,
                            'date_from' => $this->date_from,
                            'date_to' => $this->date_to,
                        ],
                    ]);
                }),
        ];
    }
}
