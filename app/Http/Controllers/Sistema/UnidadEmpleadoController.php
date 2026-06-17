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
class UnidadEmpleadoController extends Controller
{
    //******************** UNIDAD EMPLEADO *************************************************************


    public function vistaUnidadEmpleado()
    {
        $arrayDistritos = Distrito::orderBy('nombre', 'ASC')->get();

        // jefes: relación many-to-many via jefe_unidad
        $listado = UnidadEmpleado::with(['distrito', 'jefesACargo.cargo'])
            ->orderBy('nombre', 'ASC')
            ->get()
            ->map(function ($item) {
                $item->distrito = $item->distrito->nombre ?? '—';
                $item->jefes    = $item->jefesACargo;      // colección de empleados jefe
                return $item;
            });

        return view('backend.admin.empleados.unidadempleado.vistaunidadempleados',
            compact('arrayDistritos', 'listado'));
    }

    public function tablaUnidadEmpleado()
    {
        $listado = UnidadEmpleado::with(['distrito', 'jefesACargo.cargo'])
            ->orderBy('nombre', 'ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'id'       => $item->id,
                    'nombre'   => $item->nombre,
                    'distrito' => $item->distrito->nombre ?? '—',
                    'jefes'    => $item->jefesACargo->map(function ($j) {
                        return [
                            'id'     => $j->id,
                            'nombre' => $j->nombre,
                            'cargo'  => $j->cargo->nombre ?? '—',
                        ];
                    })->values()->toArray(),
                ];
            })->toArray();

        return view('backend.admin.empleados.unidadempleado.tablaunidadempleados', compact('listado'));
    }



    public function nuevoUnidadEmpleado(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'nombre' => 'required',
            'unidad' => 'required',
        ]);

        if ($validar->fails()) return ['success' => 0];

        DB::beginTransaction();
        try {
            $dato = new UnidadEmpleado();
            $dato->id_distrito = $request->unidad;
            $dato->nombre      = $request->nombre;
            $dato->save();

            DB::commit();
            return ['success' => 1];
        } catch (\Throwable $e) {
            Log::error('nuevoUnidadEmpleado: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    public function infoUnidadEmpleado(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        $info          = UnidadEmpleado::findOrFail($request->id);
        $arrayDistrito = Distrito::orderBy('nombre', 'ASC')->get();

        return ['success' => 1, 'info' => $info, 'arrayDistrito' => $arrayDistrito];
    }


    public function actualizarUnidadEmpleado(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'id'       => 'required',
            'nombre'   => 'required',
            'distrito' => 'required',
        ]);
        if ($validar->fails()) return ['success' => 0];

        UnidadEmpleado::where('id', $request->id)->update([
            'nombre'      => $request->nombre,
            'id_distrito' => $request->distrito,
        ]);

        return ['success' => 1];
    }



    public function informacionJefesUnidad(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        // Todos los empleados con jefe=true para poblar el select
        $arrayJefes = Empleado::with('cargo')
            ->where('jefe', true)
            ->orderBy('nombre', 'ASC')
            ->get()
            ->map(function ($e) {
                $e->nombre_completo = $e->nombre . ' (' . ($e->cargo->nombre ?? '—') . ')';
                return $e;
            });

        // Jefes ya asignados a esta unidad
        $asignados = DB::table('jefe_unidad')
            ->join('empleado', 'empleado.id', '=', 'jefe_unidad.id_empleado')
            ->join('cargo', 'cargo.id', '=', 'empleado.id_cargo')
            ->where('jefe_unidad.id_unidad_empleado', $request->id)
            ->select(
                'jefe_unidad.id as pivot_id',
                'empleado.nombre',
                'cargo.nombre as cargo'
            )
            ->get();

        return ['success' => 1, 'arrayJefes' => $arrayJefes, 'asignados' => $asignados];
    }

    // ── Agregar jefe a unidad ──
    public function agregarJefeUnidad(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'id_unidad'   => 'required',
            'id_empleado' => 'required',
        ]);
        if ($validar->fails()) return ['success' => 0];

        // Verificar que no esté duplicado
        $existe = DB::table('jefe_unidad')
            ->where('id_unidad_empleado', $request->id_unidad)
            ->where('id_empleado', $request->id_empleado)
            ->exists();

        if ($existe) return ['success' => 2]; // ya asignado

        DB::table('jefe_unidad')->insert([
            'id_empleado'        => $request->id_empleado,
            'id_unidad_empleado' => $request->id_unidad,
        ]);

        // Devolver lista actualizada
        $asignados = $this->getAsignados($request->id_unidad);

        return ['success' => 1, 'asignados' => $asignados];
    }


// ── Quitar jefe de unidad ──
    public function quitarJefeUnidad(Request $request)
    {
        $validar = Validator::make($request->all(), ['pivot_id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        $pivot = DB::table('jefe_unidad')->where('id', $request->pivot_id)->first();
        if (!$pivot) return ['success' => 0];

        DB::table('jefe_unidad')->where('id', $request->pivot_id)->delete();

        $asignados = $this->getAsignados($pivot->id_unidad_empleado);

        return ['success' => 1, 'asignados' => $asignados];
    }


    public function editarJefeInmediato(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()) {
            return ['success' => 0];
        }

        UnidadEmpleado::where('id', $request->id)->update([
            'id_empleado' => $request->empleadounidad,
            'id_empleado_inmediato' => $request->empleadoinmediato
        ]);

        return ['success' => 1];
    }


    private function getAsignados($idUnidad)
    {
        return DB::table('jefe_unidad')
            ->join('empleado', 'empleado.id', '=', 'jefe_unidad.id_empleado')
            ->join('cargo', 'cargo.id', '=', 'empleado.id_cargo')
            ->where('jefe_unidad.id_unidad_empleado', $idUnidad)
            ->select(
                'jefe_unidad.id as pivot_id',
                'empleado.nombre',
                'cargo.nombre as cargo'
            )
            ->get();
    }


}
