<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseInventory extends Model
{
    protected $table = 'warehouse_inventory';

    protected $fillable = [
        'item_number',
        'item_name',
        'grade',
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
}
