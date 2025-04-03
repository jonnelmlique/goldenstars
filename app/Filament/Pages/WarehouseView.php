<?php

namespace App\Filament\Pages;

use App\Models\WarehouseLocation;
use App\Models\WarehouseInventory;
use App\Models\Building;
use Filament\Pages\Page;

class WarehouseView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static string $view = 'filament.pages.warehouse-view';
    protected static ?string $navigationLabel = 'Warehouse View';
    protected static ?string $navigationGroup = 'Warehouse';
    protected static ?int $navigationSort = 1;
    public $locations;
    public $warehouseInventory;
    public $buildings;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasPermission('warehouse.view');
    }

    public function mount()
    {
        if (!auth()->user()->hasPermission('warehouse.view')) {
            return redirect()->back();
        }

        $this->buildings = Building::all();
        $this->locations = WarehouseLocation::with(['shelves.items', 'building'])->get();
        $this->warehouseInventory = WarehouseInventory::with(['shelf.location'])->get();
    }
}
