<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salidas extends Model
{
    use HasFactory;

    protected $table = 'salidas';
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_empleado',
        'descripcion',
        'area',
        'cargo',
        'colaborador',
        'jefe_inmediato',
        'material_linea',
        'jefe_firma',
        'cargo_firma',
    ];

    public function detalle()
    {
        return $this->hasMany(SalidasDetalle::class, 'id_salidas');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
