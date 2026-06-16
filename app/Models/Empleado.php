<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleado';
    public $timestamps = false;

    // A qué unidad pertenece como empleado
    public function unidadEmpleado()
    {
        return $this->belongsTo(UnidadEmpleado::class, 'id_unidad_empleado');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    // Jerarquía
    public function jefe()
    {
        return $this->belongsTo(Empleado::class, 'id_jefe');
    }

    public function subordinados()
    {
        return $this->hasMany(Empleado::class, 'id_jefe');
    }

    // Unidades que tiene a cargo (si es jefe/gerente)
    public function unidadesACargo()
    {
        return $this->belongsToMany(
            UnidadEmpleado::class,
            'jefe_unidad',
            'id_empleado',
            'id_unidad_empleado'
        );
    }



    public function jefeDirecto()
    {
        return $this->belongsTo(Empleado::class, 'id_jefe');
    }
}
