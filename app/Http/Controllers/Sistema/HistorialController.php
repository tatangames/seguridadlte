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

class HistorialController extends Controller
{
    public function indexHistorialEntradas()
    {
        return view('backend.admin.historial.entradas.vistahistorialentradas');
    }

    public function tablaHistorialEntradas(Request $request)
    {
        $arrayEntradas = Entradas::with(['proveedor', 'bodega'])
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($item) {
                $item->fecha_fmt = date('d/m/Y', strtotime($item->fecha));
                return $item;
            });

        return view('backend.admin.historial.entradas.tablahistorialentradas',
            compact('arrayEntradas'));
    }

    public function informacionEntrada(Request $request)
    {
        $entrada = Entradas::with('proveedor')->find($request->id);

        if (!$entrada) {
            return response()->json(['success' => 0]);
        }

        // Todos los proveedores para el select
        $arrayProveedor = Proveedor::orderBy('nombre')->get(['id', 'nombre']);

        return response()->json([
            'success'        => 1,
            'info'           => [
                'id'          => $entrada->id,
                'fecha'       => $entrada->fecha,   // YYYY-MM-DD para input type="date"
                'lote'        => $entrada->lote,
                'descripcion' => $entrada->descripcion,
                'id_proveedor'=> $entrada->id_proveedor,
            ],
            'arrayProveedor' => $arrayProveedor,
        ]);
    }

    public function editarEntrada(Request $request)
    {
        $entrada = Entradas::find($request->id);

        if (!$entrada) {
            return response()->json(['success' => 0]);
        }

        $entrada->fecha       = $request->fecha;
        $entrada->lote        = $request->lote        ?: null;
        $entrada->descripcion = $request->descripcion ?: null;
        $entrada->id_proveedor= $request->proveedor;
        $entrada->save();

        return response()->json(['success' => 1]);
    }

    public function eliminarEntrada(Request $request)
    {
        $entrada = Entradas::find($request->id);

        if (!$entrada) {
            return response()->json(['success' => 0]);
        }

        DB::beginTransaction();
        try {
            $idsDetalle = $entrada->detalle()->pluck('id');

            if ($idsDetalle->isNotEmpty()) {
                // Salidas afectadas
                $idsSalidas = SalidasDetalle::whereIn('id_entrada_detalle', $idsDetalle)
                    ->pluck('id_salida')
                    ->unique();

                SalidasDetalle::whereIn('id_entrada_detalle', $idsDetalle)->delete();

                if ($idsSalidas->isNotEmpty()) {
                    $salidasHuerfanas = Salidas::whereIn('id', $idsSalidas)
                        ->whereNotExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('salidas_detalle')
                                ->whereColumn('salidas.id', 'salidas_detalle.id_salida');
                        })
                        ->pluck('id');

                    if ($salidasHuerfanas->isNotEmpty()) {
                        Salidas::whereIn('id', $salidasHuerfanas)->delete();
                    }
                }

                $entrada->detalle()->delete();
            }

            $entrada->delete();

            DB::commit();
            return response()->json(['success' => 1]);

        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('eliminarEntrada: ' . $e->getMessage());
            return response()->json(['success' => 99]);
        }
    }

    public function detalleEntrada(Request $request)
    {
        $entrada = Entradas::find($request->id);

        if (!$entrada) {
            return response()->json(['success' => 0]);
        }

        $detalle = $entrada->detalle()
            ->with('material')
            ->get()
            ->map(function ($item) {
                return [
                    'id'               => $item->id,
                    'material'         => $item->material->nombre ?? '',
                    'cantidad_inicial' => $item->cantidad_inicial,
                    'precio'           => number_format($item->precio, 4),
                    'precio_raw'       => $item->precio,
                ];
            });

        return response()->json([
            'success' => 1,
            'detalle' => $detalle,
        ]);
    }

    public function editarDetalleEntrada(Request $request)
    {
        $detalle = EntradasDetalle::find($request->id);

        if (!$detalle) {
            return response()->json(['success' => 0]);
        }

        $detalle->cantidad_inicial = $request->cantidad;
        $detalle->precio           = $request->precio;
        $detalle->save();

        return response()->json(['success' => 1]);
    }

    public function eliminarDetalleEntrada(Request $request)
    {
        $detalle = EntradasDetalle::find($request->id);

        if (!$detalle) {
            return response()->json(['success' => 0]);
        }

        // Bloquear si tiene salidas
        $tieneSalidas = SalidasDetalle::where('id_entrada_detalle', $detalle->id)->exists();
        if ($tieneSalidas) {
            return response()->json([
                'success' => 4,
                'msg'     => 'Este material ya tiene salidas registradas y no puede eliminarse.',
            ]);
        }

        DB::beginTransaction();
        try {
            $entradaId = $detalle->id_entradas;
            $detalle->delete();

            $quedan = EntradasDetalle::where('id_entradas', $entradaId)->count();

            if ($quedan === 0) {
                Entradas::where('id', $entradaId)->delete();
                DB::commit();
                return response()->json(['success' => 1, 'entrada_borrada' => true]);
            }

            DB::commit();
            return response()->json(['success' => 1, 'entrada_borrada' => false]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => 99, 'msg' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }



    public function vistaExtrasEntrada($id)
    {
        $entrada = Entradas::with('proveedor')->find($id);

        if (!$entrada) {
            return redirect()->route('admin.historial.entradas.index')
                ->with('error', 'Entrada no encontrada');
        }

        return view('backend.admin.historial.entradas.vistaextras', compact('entrada'));
    }

    public function guardarExtrasEntrada(Request $request)
    {
        $entrada = Entradas::find($request->id_entrada);

        if (!$entrada) {
            return response()->json(['success' => 0]);
        }

        $contenedor = json_decode($request->contenedorArray, true);

        if (empty($contenedor)) {
            return response()->json(['success' => 0]);
        }

        foreach ($contenedor as $item) {
            EntradasDetalle::create([
                'id_entradas'      => $entrada->id,
                'id_material'      => $item['idMaterial'],
                'cantidad_inicial' => $item['infoCantidad'],
                'precio'           => $item['infoPrecio'],
            ]);
        }

        return response()->json(['success' => 2]);
    }



















}
