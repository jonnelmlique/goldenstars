<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseShelf extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'code',
        'level',
        'capacity',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WarehouseInventory::class, 'shelf_id');
    }
}
