<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entradas extends Model
{
    use HasFactory;

    protected $table = 'entradas';
    public $timestamps = false;

    protected $fillable = ['id_proveedor','fecha', 'descripcion', 'lote', 'id_bodega'];


    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function detalle()
    {
        return $this->hasMany(EntradasDetalle::class, 'id_entradas');
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class, 'id_bodega');
    }
}
