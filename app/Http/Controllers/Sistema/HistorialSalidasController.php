<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Distrito;
use App\Models\Empleado;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\InformacionGeneral;
use App\Models\Materiales;
use App\Models\Proveedor;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HistorialSalidasController extends Controller
{
    public function indexHistorialSalidas()
    {
        $arrayDistrito  = Distrito::orderBy('nombre')->get();
        $arrayEmpleados = Empleado::orderBy('nombre')->get();

        return view('backend.admin.historial.salidas.vistahistorialsalidas',
            compact('arrayDistrito', 'arrayEmpleados'));
    }

    public function tablaHistorialSalidas(Request $request)
    {
        if (!$request->filled('buscar_todos')) {
            $arraySalidas = collect();
            return view('backend.admin.historial.salidas.tablahistorialsalidas',
                compact('arraySalidas'));
        }

        $query = Salidas::with(['empleado']);

        if ($request->filled('id_empleado')) {
            $query->where('id_empleado', $request->id_empleado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // ── NUEVO: filtro por material (nombre o código) ──────────────
        if ($request->filled('material')) {
            $busqueda = '%' . $request->material . '%';
            $query->whereHas('detalles.entradaDetalle.material', function ($q2) use ($busqueda) {
                $q2->where('nombre', 'LIKE', $busqueda)
                    ->orWhere('codigo', 'LIKE', $busqueda);
            });
        }
        // ───────────────────────────────────────────────────────────

        $arraySalidas = $query->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($item) {
                $item->fecha_fmt = date('d/m/Y', strtotime($item->fecha));
                return $item;
            });

        return view('backend.admin.historial.salidas.tablahistorialsalidas',
            compact('arraySalidas'));
    }

    public function informacionSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        $datos = DB::table('empleado as e')
            ->join('unidad_empleado as ue', 'ue.id', '=', 'e.id_unidad_empleado')
            ->join('distrito as d',         'd.id',  '=', 'ue.id_distrito')
            ->where('e.id', $salida->id_empleado)
            ->select('e.id as id_empleado', 'ue.id as id_unidad', 'd.id as id_distrito')
            ->first();

        return response()->json([
            'success' => 1,
            'salida'  => [
                'id'                 => $salida->id,
                'fecha'              => $salida->fecha,
                'descripcion'        => $salida->descripcion,
                'jefe_firma'         => $salida->jefe_firma,
                'cargo_firma'        => $salida->cargo_firma,
                'material_linea'     => $salida->material_linea,
                'id_empleado'        => $datos->id_empleado ?? null,
                'id_unidad_empleado' => $datos->id_unidad   ?? null,
                'id_distrito'        => $datos->id_distrito ?? null,
            ]
        ]);
    }

    public function datosEmpleado(Request $request)
    {
        $empleado = Empleado::with('cargo', 'jefe')->find($request->id);

        if (!$empleado) {
            return response()->json(['success' => 0]);
        }

        return response()->json([
            'success'  => 1,
            'empleado' => [
                'id'           => $empleado->id,
                'nombre'       => $empleado->nombre,
                'cargo_nombre' => $empleado->cargo->nombre ?? null,
                'jefe_nombre'  => $empleado->jefe->nombre  ?? null,
            ]
        ]);
    }

    public function editarSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        // ── Validar mes actual ──────────────────────────────────────────
        $fechaNueva  = Carbon::parse($request->fecha);
        $ahora       = Carbon::now();
        $esMesActual = $fechaNueva->month === $ahora->month
            && $fechaNueva->year  === $ahora->year;

        if (!$esMesActual) {
            return response()->json([
                'success' => 0,
                'msg'     => 'Solo se pueden editar salidas del mes actual.',
            ]);
        }

        // ── Validar que la fecha no sea anterior al ingreso de algún ítem ──
        $entradaConflicto = DB::table('salidas_detalle as sd')
            ->join('entradas_detalle as ed', 'ed.id', '=', 'sd.id_entrada_detalle')
            ->join('entradas as e',          'e.id',  '=', 'ed.id_entradas')
            ->join('materiales as m',        'm.id',  '=', 'ed.id_material')
            ->where('sd.id_salida', $salida->id)
            ->where('e.fecha', '>', $request->fecha)
            ->orderBy('e.fecha', 'desc')
            ->select('m.nombre as nombre_material', 'e.fecha as fecha_ingreso')
            ->first();

        if ($entradaConflicto) {
            return response()->json([
                'success'         => 2,
                'nombre_material' => $entradaConflicto->nombre_material,
                'fecha_salida'    => Carbon::parse($request->fecha)->format('d-m-Y'),
                'fecha_ingreso'   => Carbon::parse($entradaConflicto->fecha_ingreso)->format('d-m-Y'),
            ]);
        }

        $salida->fecha          = $request->fecha;
        $salida->descripcion    = $request->descripcion    ?: null;
        $salida->jefe_firma     = $request->jefe_firma     ?: null;
        $salida->cargo_firma    = $request->cargo_firma    ?: null;
        $salida->material_linea = $request->material_linea ?: null;

        if ((int)$request->id_empleado !== (int)$salida->id_empleado) {

            $empleadoDatos = DB::table('empleado as e')
                ->join('unidad_empleado as ue', 'ue.id', '=', 'e.id_unidad_empleado')
                ->join('cargo as c',            'c.id',  '=', 'e.id_cargo')
                ->leftJoin('jefe_unidad as ju', 'ju.id_unidad_empleado', '=', 'e.id_unidad_empleado')
                ->leftJoin('empleado as jefe',  'jefe.id', '=', 'ju.id_empleado')
                ->where('e.id', $request->id_empleado)
                ->select(
                    'e.id',
                    'e.nombre    as nombre_empleado',
                    'ue.nombre   as nombre_unidad',
                    'c.nombre    as nombre_cargo',
                    'jefe.nombre as nombre_jefe'
                )
                ->first();

            if (!$empleadoDatos) {
                return response()->json(['success' => 0]);
            }

            $salida->id_empleado    = $empleadoDatos->id;
            $salida->area           = $empleadoDatos->nombre_unidad;
            $salida->cargo          = $empleadoDatos->nombre_cargo;
            $salida->colaborador    = $empleadoDatos->nombre_empleado;
            $salida->jefe_inmediato = $empleadoDatos->nombre_jefe;
        }

        $salida->save();

        return response()->json(['success' => 1]);
    }

    public function eliminarSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        // ── Validar mes actual ──────────────────────────────────────────
        $ahora       = Carbon::now();
        $fechaSalida = Carbon::parse($salida->fecha);
        $esMesActual = $fechaSalida->month === $ahora->month
            && $fechaSalida->year  === $ahora->year;

        if (!$esMesActual) {
            return response()->json([
                'success' => 0,
                'msg'     => 'Solo se pueden eliminar salidas del mes actual.',
            ]);
        }

        SalidasDetalle::where('id_salida', $salida->id)->delete();
        $salida->delete();

        return response()->json(['success' => 1]);
    }

    public function detalleSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        $detalle = SalidasDetalle::where('id_salida', $salida->id)
            ->with('entradaDetalle.material')
            ->get()
            ->map(function ($item) {
                return [
                    'id_detalle'      => $item->id,
                    'material'        => $item->entradaDetalle->material->nombre ?? '—',
                    'unidad'          => $item->entradaDetalle->material->unidadMedida->nombre ?? '—',
                    'cantidad_salida' => $item->cantidad_salida,
                    'precio'          => number_format($item->entradaDetalle->precio ?? 0, 4),
                ];
            });

        // ── Verificar mes actual comparando enteros (evita desfase de zona horaria) ──
        $ahora       = Carbon::now();
        $fechaSalida = Carbon::parse($salida->fecha);
        $esMesActual = $fechaSalida->month === $ahora->month
            && $fechaSalida->year  === $ahora->year;

        return response()->json([
            'success'       => 1,
            'detalle'       => $detalle,
            'es_mes_actual' => $esMesActual,
        ]);
    }

    public function vistaExtrasSalida($id)
    {
        $salida = Salidas::with('empleado')->find($id);

        if (!$salida) {
            return redirect()->route('admin.historial.salidas.index')
                ->with('error', 'Salida no encontrada');
        }

        return view('backend.admin.historial.salidas.vistaextrassalidas', compact('salida'));
    }

    public function guardarExtrasSalida(Request $request)
    {
        $salida = Salidas::find($request->id_salida);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        $contenedor = json_decode($request->contenedorArray, true);

        if (empty($contenedor)) {
            return response()->json(['success' => 0]);
        }

        DB::beginTransaction();

        try {
            $filaContada = 0;

            foreach ($contenedor as $item) {
                $filaContada++;

                $entradasDetalle = EntradasDetalle::find($item['infoIdEntradaDeta']);

                if (!$entradasDetalle) {
                    DB::rollback();
                    return response()->json(['success' => 2, 'fila' => $filaContada]);
                }

                $totalSalido = DB::table('salidas_detalle')
                    ->where('id_entrada_detalle', $entradasDetalle->id)
                    ->sum('cantidad_salida');

                $disponibleReal = $entradasDetalle->cantidad_inicial - $totalSalido;

                if ($item['infoCantidad'] > $disponibleReal) {
                    DB::rollback();
                    return response()->json([
                        'success'    => 2,
                        'fila'       => $filaContada,
                        'disponible' => $disponibleReal,
                        'solicitado' => $item['infoCantidad'],
                    ]);
                }

                SalidasDetalle::create([
                    'id_salida'          => $salida->id,
                    'id_entrada_detalle' => $entradasDetalle->id,
                    'cantidad_salida'    => $item['infoCantidad'],
                    'tipo_regresa'       => 0,
                    'reemplazo'          => 0,
                    'recomendacion'      => 0,
                ]);
            }

            DB::commit();
            return response()->json(['success' => 10]);

        } catch (\Throwable $e) {
            Log::error('guardarExtrasSalida: ' . $e);
            DB::rollback();
            return response()->json(['success' => 99]);
        }
    }

    public function eliminarItemDetalleSalida(Request $request)
    {
        $detalle = SalidasDetalle::find($request->id_detalle);

        if (!$detalle) {
            return response()->json(['success' => 0]);
        }

        $salida = Salidas::find($detalle->id_salida);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        // ── Validar mes actual en el servidor (fuente de verdad real) ──
        $ahora       = Carbon::now();
        $fechaSalida = Carbon::parse($salida->fecha);
        $esMesActual = $fechaSalida->month === $ahora->month
            && $fechaSalida->year  === $ahora->year;

        if (!$esMesActual) {
            return response()->json([
                'success' => 0,
                'msg'     => 'Solo se pueden eliminar ítems del mes actual.',
            ]);
        }

        $idSalida = $detalle->id_salida;
        $detalle->delete();

        $itemsRestantes = SalidasDetalle::where('id_salida', $idSalida)->count();

        if ($itemsRestantes === 0) {
            Salidas::where('id', $idSalida)->delete();
        }

        return response()->json(['success' => 1]);
    }
}
