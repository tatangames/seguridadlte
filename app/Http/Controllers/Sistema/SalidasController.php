<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Departamentos;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\InformacionGeneral;
use App\Models\Materiales;
use App\Models\Reserva;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\TipoProyecto;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SalidasController extends Controller
{

    public function indexRegistroSalida(){

        $arrayProyectos = Tipoproyecto::where('transferido', 0)->orderBy('nombre')->get();

        return view('backend.admin.repuestos.salidas.vistasalidaregistro', compact('arrayProyectos'));
    }


    public function buscadorMaterialDisponible(Request $request)
    {

        Log::info($request->all());

        if ($request->get('query')) {

            $query      = $request->get('query');
            $idProyecto = $request->get('id_proyecto');

            $materiales = Materiales::where('nombre', 'LIKE', "%{$query}%")->pluck('id');

            if ($materiales->isEmpty()) {
                return '';
            }

            $listado = DB::table('entradas_detalle as ed')
                ->join('entradas as e', 'e.id', '=', 'ed.id_entradas') // 👈 JOIN a entradas
                ->leftJoin(
                    DB::raw('(
                    SELECT id_entrada_detalle, SUM(cantidad_salida) as total_salido
                    FROM salidas_detalle
                    GROUP BY id_entrada_detalle
                ) as sd'),
                    'sd.id_entrada_detalle', '=', 'ed.id'
                )
                ->select(
                    'ed.id_material',
                    DB::raw('SUM(ed.cantidad_inicial) as total_inicial'),
                    DB::raw('COALESCE(SUM(sd.total_salido), 0) as total_salido'),
                    DB::raw('(SUM(ed.cantidad_inicial) - COALESCE(SUM(sd.total_salido), 0)) as disponible')
                )
                ->whereIn('ed.id_material', $materiales)
                ->when($idProyecto, fn($q) => $q->where('e.id_tipoproyecto', $idProyecto)) // 👈 filtro por proyecto
                ->groupBy('ed.id_material')
                ->havingRaw('disponible > 0')
                ->orderBy('ed.id_material')
                ->get();

            if ($listado->isEmpty()) {
                return '';
            }

            $output = '<ul class="dropdown-menu" style="display:block; position:relative; overflow:auto; max-height:300px; width:800px">';

            foreach ($listado as $row) {

                $infoMaterial = Materiales::with(['unidadMedida'])
                    ->find($row->id_material);

                if (!$infoMaterial) continue;

                $nombreCompleto = $infoMaterial->nombre .
                    " (" . optional($infoMaterial->unidadMedida)->nombre . ")";

                $output .= '
                <li class="cursor-pointer" onclick="modificarValor(this)"
                    id="' . $row->id_material . '"
                    data-tipo="material">
                    ' . $nombreCompleto . ' - Disponible: ' . $row->disponible . '
                </li>
                <hr>
            ';
            }

            $output .= '</ul>';

            return $output;
        }
    }



    public function infoBodegaMaterialDetalleFila(Request $request)
    {
        $regla = ['id' => 'required'];

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) {
            return ['success' => 0];
        }

        $infoMaterial = Materiales::find($request->id);
        if (!$infoMaterial) {
            return ['success' => 0];
        }

        $infoMedida   = UnidadMedida::find($infoMaterial->id_medida);
        $idProyecto   = $request->get('id_proyecto'); // 👈

        $listado = DB::table('entradas_detalle as ed')
            ->leftJoin(
                DB::raw('(
                SELECT id_entrada_detalle, SUM(cantidad_salida) as total_salido
                FROM salidas_detalle
                GROUP BY id_entrada_detalle
            ) as sd'),
                'sd.id_entrada_detalle', '=', 'ed.id'
            )
            ->join('entradas as e', 'e.id', '=', 'ed.id_entradas') // 👈 INNER JOIN (antes era left)
            ->select(
                'ed.id',
                'ed.id_entradas',
                'ed.cantidad_inicial',
                'ed.precio',
                'e.fecha',
                'ed.codigo',
                DB::raw('COALESCE(sd.total_salido, 0) as total_salido'),
                DB::raw('(ed.cantidad_inicial - COALESCE(sd.total_salido, 0)) as cantidadActual')
            )
            ->where('ed.id_material', $request->id)
            ->when($idProyecto, fn($q) => $q->where('e.id_tipoproyecto', $idProyecto)) // 👈
            ->havingRaw('cantidadActual > 0')
            ->orderBy('ed.id')
            ->get();

        foreach ($listado as $fila) {
            $fila->fechaIngreso = date("d-m-Y", strtotime($fila->fecha));
            $fila->precioFormat = '$' . number_format($fila->precio, 2, '.', ',');
        }

        $disponible = $listado->isEmpty() ? 1 : 0;

        return [
            'success'        => 1,
            'nombreMaterial' => $infoMaterial->nombre ?? '',
            'nombreMedida'   => $infoMedida->nombre   ?? '',
            'arrayIngreso'   => $listado,
            'disponible'     => $disponible,
        ];
    }













    public function guardarSalida(Request $request)
    {
        $rules = [
            'fecha'    => 'required',
            'proyecto' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ['success' => 0];
        }

        $contenedor = json_decode($request->contenedorArray, true);

        if (empty($contenedor)) {
            return ['success' => 1];
        }

        // ── Validar que el proyecto no esté cerrado ──
        $proyecto = Tipoproyecto::find($request->proyecto);
        if (!$proyecto || $proyecto->transferido == 1) {
            return ['success' => 3]; // proyecto cerrado
        }

        // ✅ Agrupar por id_entrada_detalle y sumar cantidades del mismo lote
        $agrupado = [];
        foreach ($contenedor as $item) {
            $id = $item['infoIdEntradaDeta'];
            if (!isset($agrupado[$id])) {
                $agrupado[$id] = 0;
            }
            $agrupado[$id] += (int) $item['infoCantidad'];
        }

        DB::beginTransaction();

        try {
            $fila = 1;
            // ── Validar disponibilidad ──
            foreach ($agrupado as $idEntradaDetalle => $cantidadSalida) {

                $disponible = DB::table('entradas_detalle as ed')
                    ->leftJoin(
                        DB::raw('(
                SELECT id_entrada_detalle, SUM(cantidad_salida) as total_salido
                FROM salidas_detalle
                GROUP BY id_entrada_detalle
            ) as sd'),
                        'sd.id_entrada_detalle', '=', 'ed.id'
                    )
                    ->where('ed.id', $idEntradaDetalle)
                    ->selectRaw('(ed.cantidad_inicial - COALESCE(sd.total_salido, 0)) as disponible')
                    ->value('disponible');

                if (is_null($disponible) || $cantidadSalida > $disponible) {
                    DB::rollback();

                    $nombreMaterial = DB::table('entradas_detalle as ed')
                        ->join('materiales as m', 'm.id', '=', 'ed.id_material')
                        ->where('ed.id', $idEntradaDetalle)
                        ->value('m.nombre');

                    return [
                        'success'         => 2,
                        'fila'            => $fila,
                        'nombre_material' => $nombreMaterial ?? 'Material desconocido',
                        'cantidad_pedida' => $cantidadSalida,
                        'disponible'      => (int) $disponible,
                    ];
                }

                // ── 🆕 Validar que la fecha de salida no sea anterior a la fecha de ingreso ──
                $fechaIngreso = DB::table('entradas_detalle as ed')
                    ->join('entradas as e', 'e.id', '=', 'ed.id_entradas')
                    ->where('ed.id', $idEntradaDetalle)
                    ->value('e.fecha');

                if ($fechaIngreso && Carbon::parse($request->fecha)->lt(Carbon::parse($fechaIngreso))) {
                    DB::rollback();

                    $nombreMaterial = DB::table('entradas_detalle as ed')
                        ->join('materiales as m', 'm.id', '=', 'ed.id_material')
                        ->where('ed.id', $idEntradaDetalle)
                        ->value('m.nombre');

                    return [
                        'success'          => 4,
                        'nombre_material'  => $nombreMaterial ?? 'Material desconocido',
                        'fecha_salida'     => Carbon::parse($request->fecha)->format('d-m-Y'),
                        'fecha_ingreso'    => Carbon::parse($fechaIngreso)->format('d-m-Y'),
                    ];
                }
                // ─────────────────────────────────────────────────────────────────────────

                $fila++;
            }

            // Guardar cabecera
            $salida                  = new Salidas();
            $salida->fecha           = Carbon::parse($request->fecha);
            $salida->descripcion     = $request->descripcion;
            $salida->id_tipoproyecto = $request->proyecto;
            $salida->es_transferencia= 0;
            $salida->id_tipoproyecto_transferencia = null;
            $salida->ficha_nombre = $request->fichaNombre;
            $salida->ficha_talonario = $request->fichaTalonario;
            $salida->save();

            // ✅ Guardar detalle con cantidades agrupadas
            foreach ($agrupado as $idEntradaDetalle => $cantidadSalida) {
                $detalle                      = new SalidasDetalle();
                $detalle->id_salida           = $salida->id;
                $detalle->id_entrada_detalle  = $idEntradaDetalle;
                $detalle->cantidad_salida     = $cantidadSalida;
                $detalle->save();
            }

            DB::commit();
            return ['success' => 10];

        } catch (\Throwable $e) {
            Log::error('guardarSalida: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }



















}
