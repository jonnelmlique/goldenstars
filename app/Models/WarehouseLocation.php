<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'x_position',
        'y_position',
        'z_position',
        'building_id', // Ensure this is included in the fillable array
    ];

    public function shelves(): HasMany
    {
        return $this->hasMany(WarehouseShelf::class, 'location_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
