<?php

namespace App\Filament\Pages;

use App\Models\WarehouseLocation;
use App\Models\WarehouseInventory;
use Filament\Pages\Page;

class WarehouseView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static string $view = 'filament.pages.warehouse-view';
    protected static ?string $navigationLabel = '3D Warehouse View';
    protected static ?int $navigationSort = 4;
    public $locations;
    public $warehouseInventory;

    public function mount()
    {
        $this->locations = WarehouseLocation::with(['shelves.items'])->get();
        $this->warehouseInventory = WarehouseInventory::with(['shelf.location'])->get();
    }


}
