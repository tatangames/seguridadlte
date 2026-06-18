<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
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
        return view('backend.admin.historial.salidas.vistahistorialsalidas');
    }

    public function tablaHistorialSalidas(Request $request)
    {
        $arraySalidas = Salidas::with(['empleado'])
            ->orderBy('fecha', 'desc')
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

        return response()->json([
            'success' => 1,
            'salida'  => [
                'id'          => $salida->id,
                'fecha'       => $salida->fecha,
                'descripcion' => $salida->descripcion,
            ]
        ]);
    }

    public function editarSalida(Request $request)
    {
        $salida = Salidas::find($request->id);

        if (!$salida) {
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

        $salida->fecha       = $request->fecha;
        $salida->descripcion = $request->descripcion ?: null;
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



}
