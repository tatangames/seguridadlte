<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Color;
use App\Models\Cuenta;
use App\Models\Departamentos;
use App\Models\Distrito;
use App\Models\Empleado;
use App\Models\JefeFirma;
use App\Models\Marca;
use App\Models\Normativa;
use App\Models\ObjetoEspecifico;
use App\Models\Proveedor;
use App\Models\Rubro;
use App\Models\Talla;
use App\Models\UnidadEmpleado;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmpleadoController extends Controller
{
    public function index()
    {
        $arrayDistrito  = Distrito::orderBy('nombre')->get();
        $arrayCargo     = Cargo::orderBy('nombre')->get();
        $arrayEmpleados = Empleado::where('jefe', 1)->orderBy('nombre')->get();

        return view('backend.admin.empleados.empleados.vistaempleados',
            compact('arrayDistrito', 'arrayCargo', 'arrayEmpleados'));
    }

    public function tabla()
    {
        $listado = Empleado::with(['unidadEmpleado.distrito', 'cargo', 'jefeDirecto'])
            ->orderBy('nombre', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'nombre'     => $item->nombre,
                    'distrito'   => $item->unidadEmpleado->distrito->nombre ?? '—',
                    'unidad'     => $item->unidadEmpleado->nombre            ?? '—',
                    'cargo'      => $item->cargo->nombre                     ?? '—',
                    'dui'        => $item->dui                               ?? '—',
                    'jefe'       => $item->jefe ? 1 : 0,
                    'activo'     => $item->activo ? 1 : 0,
                    'jefe_nombre'=> $item->jefeDirecto->nombre               ?? '—',
                ];
            })->toArray();

        return view('backend.admin.empleados.empleados.tablaempleados', compact('listado'));
    }

    public function buscarUnidad(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        $arrayUnidad = UnidadEmpleado::where('id_distrito', $request->id)
            ->orderBy('nombre')->get();

        return ['success' => 1, 'arrayUnidad' => $arrayUnidad];
    }

    public function informacion(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        $info    = Empleado::with('unidadEmpleado')->findOrFail($request->id);
        $idDist  = $info->unidadEmpleado?->id_distrito ?? null;

        $arrayUnidad = $idDist
            ? UnidadEmpleado::where('id_distrito', $idDist)->orderBy('nombre')->get()
            : collect();

        $arrayEmpleados = Empleado::with('cargo')
            ->where('jefe', 1)
            ->where('id', '!=', $request->id)
            ->orderBy('nombre')
            ->get()
            ->map(function ($e) {
                $e->nombre_completo = $e->nombre . ' (' . ($e->cargo->nombre ?? '—') . ')';
                return $e;
            });

        return [
            'success'        => 1,
            'info'           => $info,
            'idDistrito'     => $idDist,
            'arrayUnidad'    => $arrayUnidad,
            'arrayEmpleados' => $arrayEmpleados,
        ];
    }

    public function nuevo(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'nombre' => 'required',
            'unidad' => 'required',
            'cargo'  => 'required',
            'jefe'   => 'required',
        ]);
        if ($validar->fails()) return ['success' => 0];

        DB::beginTransaction();
        try {
            $dato                    = new Empleado();
            $dato->nombre            = $request->nombre;
            $dato->id_unidad_empleado = $request->unidad;
            $dato->id_cargo          = $request->cargo;
            $dato->jefe              = $request->jefe;
            $dato->dui               = $request->dui;
            $dato->id_jefe           = $request->id_jefe ?: null;
            $dato->save();

            DB::commit();
            return ['success' => 1];
        } catch (\Throwable $e) {
            Log::error('nuevo empleado: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }

    public function actualizar(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'id'     => 'required',
            'nombre' => 'required',
            'unidad' => 'required',
            'cargo'  => 'required',
            'jefe'   => 'required',
        ]);
        if ($validar->fails()) return ['success' => 0];

        Empleado::where('id', $request->id)->update([
            'nombre'              => $request->nombre,
            'id_unidad_empleado'  => $request->unidad,
            'id_cargo'            => $request->cargo,
            'jefe'                => $request->jefe,
            'dui'                 => $request->dui,
            'id_jefe'             => $request->id_jefe ?: null,
            'activo'              => $request->activo ?? 1,
        ]);

        return ['success' => 1];
    }
}
