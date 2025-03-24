<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseInventory extends Model
{
    protected $table = 'warehouse_inventory';

    protected $fillable = [
        'shelf_id',
        'name',
        'sku',
        'description',
        'quantity',
        'unit',
        'shelf_position',
    ];

    public function shelf(): BelongsTo
    {
        return $this->belongsTo(WarehouseShelf::class);
    }
}
