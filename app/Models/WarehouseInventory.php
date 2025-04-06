<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Picqer\Barcode\BarcodeGeneratorPNG;

class WarehouseInventory extends Model
{
    protected $table = 'warehouse_inventory';

    protected $fillable = [
        'item_number',
        'item_name',
        'batch_number',
        'location_code',
        'bom_unit',
        'physical_inventory',
        'physical_reserved',
        'actual_count',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(WarehouseShelf::class, 'location_code', 'location_code');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(WarehouseTransfer::class, 'inventory_id');
    }

    public function warehouseTransfers()
    {
        return $this->hasMany(WarehouseTransfer::class, 'inventory_id');
    }

    public function hasPendingTransfer(): bool
    {
        return $this->warehouseTransfers()->where('status', 'pending')->exists();
    }

    public function getBarcode($width = 2, $height = 50): string
    {
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($this->item_number, $generator::TYPE_CODE_128, $width, $height);
        return 'data:image/png;base64,' . base64_encode($barcode);
    }

    public function getBarcodeImage($width = 2, $height = 50): string
    {
        // Simple, direct approach to generate barcode
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode(
            $this->item_number,
                $generator::TYPE_CODE_128,
            $width,
            $height
        );

        return 'data:image/png;base64,' . base64_encode($barcode);
    }
}
