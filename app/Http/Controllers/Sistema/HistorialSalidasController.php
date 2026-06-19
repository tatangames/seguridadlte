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
        $arrayDistrito = Distrito::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.historial.salidas.vistahistorialsalidas',
            compact('arrayDistrito'));
    }

    public function tablaHistorialSalidas(Request $request)
    {
        // Solo bloquear si no vino del botón Buscar
        if (!$request->filled('buscar_todos')) {
            $arraySalidas = collect();
            return view('backend.admin.historial.salidas.tablahistorialsalidas',
                compact('arraySalidas'));
        }

        $query = Salidas::with(['empleado']);

        if ($request->filled('id_empleado')) {
            $query->where('id_empleado', $request->id_empleado);
        } elseif ($request->filled('id_unidad')) {
            $query->whereHas('empleado', function ($q) use ($request) {
                $q->where('id_unidad_empleado', $request->id_unidad);
            });
        } elseif ($request->filled('id_distrito')) {
            $query->whereHas('empleado.unidadEmpleado', function ($q) use ($request) {
                $q->where('id_distrito', $request->id_distrito);
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

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
        $salida = Salidas::with('empleado.unidadEmpleado.distrito', 'empleado.cargo', 'empleado.jefe')
            ->find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        $empleado = $salida->empleado;

        return response()->json([
            'success' => 1,
            'salida'  => [
                'id'                 => $salida->id,
                'fecha'              => $salida->fecha,
                'descripcion'        => $salida->descripcion,
                'cargo'              => $salida->cargo,
                'colaborador'        => $salida->colaborador,
                'jefe_inmediato'     => $salida->jefe_inmediato,
                'jefe_firma'         => $salida->jefe_firma,
                'cargo_firma'        => $salida->cargo_firma,
                'material_linea'     => $salida->material_linea,

                // Para precargar la cascada distrito -> unidad -> empleado
                'id_empleado'        => $empleado->id ?? null,
                'id_unidad_empleado' => $empleado->unidadEmpleado->id ?? null,
                'id_distrito'        => $empleado->unidadEmpleado->distrito->id ?? null,
            ]
        ]);
    }

    /**
     * Devuelve nombre, cargo y jefe inmediato de un empleado puntual.
     * Usado para repoblar los campos de solo lectura al cambiar el select de empleado.
     */
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

        $empleado = Empleado::with('cargo', 'jefe')->find($request->id_empleado);

        if (!$empleado) {
            return response()->json(['success' => 0]);
        }

        // Validar que la nueva fecha no sea anterior al ingreso de ningún ítem
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
        $salida->id_empleado    = $empleado->id;
        $salida->descripcion    = $request->descripcion    ?: null;
        $salida->cargo          = $empleado->cargo->nombre ?? null;
        $salida->colaborador    = $request->colaborador    ?: $empleado->nombre;
        $salida->jefe_inmediato = $empleado->jefe->nombre  ?? null;
        $salida->jefe_firma     = $request->jefe_firma     ?: null;
        $salida->cargo_firma    = $request->cargo_firma    ?: null;
        $salida->material_linea = $request->material_linea ?: null;
        $salida->save();

        return response()->json(['success' => 1]);
    }

    public function eliminarSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
            return response()->json(['success' => 0]);
        }

        // salidas_detalle usa id_salida (FK correcta)
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
                    'cantidad_salida' => $item->cantidad_salida,
                    'precio'          => number_format($item->entradaDetalle->precio ?? 0, 4),
                ];
            });

        return response()->json([
            'success' => 1,
            'detalle' => $detalle,
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

                // ── VALIDACIÓN: Supera disponible ──────────────────────────
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


    /**
     * Eliminar un ítem del detalle de salida.
     * Si era el último ítem, elimina también la cabecera de la salida.
     */
    public function eliminarItemDetalleSalida(Request $request)
    {
        $detalle = SalidasDetalle::find($request->id_detalle);

        if (!$detalle) {
            return response()->json(['success' => 0]);
        }

        $idSalida = $detalle->id_salida;

        // Eliminar el ítem
        $detalle->delete();

        // Verificar si quedan más ítems en esta salida
        $itemsRestantes = SalidasDetalle::where('id_salida', $idSalida)->count();

        if ($itemsRestantes === 0) {
            // Era el último → eliminar también la cabecera
            Salidas::where('id', $idSalida)->delete();
        }

        return response()->json(['success' => 1]);
    }
}
