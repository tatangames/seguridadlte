<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadEmpleado extends Model
{
    use HasFactory;

    protected $table = 'unidad_empleado';
    public $timestamps = false;

    // Distrito al que pertenece esta unidad
    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'id_distrito');
    }

    // Todos los empleados de esta unidad
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_unidad_empleado');
    }

    // Jefes asignados formalmente a esta unidad via tabla pivote jefe_unidad
    // Esta es la relación que usa vistaUnidadEmpleado() con with(['jefesACargo'])
    public function jefesACargo()
    {
        return $this->belongsToMany(
            Empleado::class,
            'jefe_unidad',          // tabla pivote
            'id_unidad_empleado',   // FK de esta tabla en el pivote
            'id_empleado'           // FK del empleado en el pivote
        );
    }
}
