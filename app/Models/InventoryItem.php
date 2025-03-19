<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'item_name',
        'model',
        'serial',
        'department_id',
        'building_id',
        'assigned_to',
        'custom_assigned_to',
        'is_defective',
        'date_transferred',
        'notes',
    ];

    protected $casts = [
        'is_defective' => 'boolean',
        'date_transferred' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
