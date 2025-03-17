<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'location'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
