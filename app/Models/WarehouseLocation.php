<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'x_position',
        'y_position',
        'z_position',
    ];

    public function shelves(): HasMany
    {
        return $this->hasMany(WarehouseShelf::class, 'location_id');
    }
}
