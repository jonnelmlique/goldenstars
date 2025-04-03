<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseTransfer extends Model
{
    protected $fillable = [
        'inventory_id',
        'from_location',
        'to_location',
        'quantity',
        'transfer_date',
        'received_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'received_date' => 'date',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(WarehouseInventory::class, 'inventory_id');
    }
}
