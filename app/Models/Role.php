<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
