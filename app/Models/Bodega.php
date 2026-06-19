<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    use HasFactory;

    protected $table = 'bodega';
    public $timestamps = false;

    public function entradas()
    {
        return $this->hasMany(Entradas::class, 'id_bodega');
    }
}
