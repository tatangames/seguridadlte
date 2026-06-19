<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Departamentos;
use App\Models\Distrito;
use App\Models\Empleado;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\InformacionGeneral;
use App\Models\Marca;
use App\Models\Materiales;
use App\Models\Normativa;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\UnidadEmpleado;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportesController extends Controller
{


    public function vistaReporteGenerales()
    {
        $arrayDistritos = Distrito::orderBy('nombre')->get(); // ajusta el modelo

        return view('backend.admin.reportes.vistareportegenerales', compact('arrayDistritos'));
    }


    public function buscarUnidadConDistritoEmpleadoReporte(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        // ✅ AHORA — jefe desde tabla jefe_unidad (pivote)
        $jefeDeUnidad = DB::table('jefe_unidad')
            ->join('empleado', 'jefe_unidad.id_empleado', '=', 'empleado.id')
            ->where('jefe_unidad.id_unidad_empleado', $request->id)
            ->pluck('empleado.nombre')
            ->implode(' / ');

        $arrayEmpleados = Empleado::with(['cargo', 'jefeDirecto'])
            ->where('id_unidad_empleado', $request->id)
            ->where('activo', 1)
            ->get()
            ->map(function ($item) use ($jefeDeUnidad) {

                $item->nombreCompleto = $item->nombre . ' (' . ($item->cargo->nombre ?? '—') . ')';

                if ($item->jefe == 1) {
                    // Soy jefe → mi superior sigue siendo la autorreferencia
                    $item->jefe_nombre = $item->jefeDirecto?->nombre ?? 'Sin jefe superior';
                } else {
                    // Soy empleado → jefe viene de jefe_unidad
                    $item->jefe_nombre = $jefeDeUnidad ?: 'Sin jefe asignado';  // ← sin ->nombre
                }

                return $item;
            });

        return ['success' => 1, 'arrayEmpleados' => $arrayEmpleados];
    }



    public function reporteEmpleadoRecibidos($idempleado)
    {
        // ── Validar que existan los datos base ─────────────────────────
        $infoEmpleado = Empleado::find($idempleado);
        if (!$infoEmpleado) abort(404, 'Empleado no encontrado');

        $infoUnidadEmpleado = UnidadEmpleado::find($infoEmpleado->id_unidad_empleado);
        if (!$infoUnidadEmpleado) abort(404, 'Unidad del empleado no encontrada');

        $infoDistrito = Distrito::find($infoUnidadEmpleado->id_distrito);
        if (!$infoDistrito) abort(404, 'Distrito no encontrado');

        // ── Salidas del empleado ───────────────────────────────────────
        $arraySalidas = Salidas::where('id_empleado', $idempleado)
            ->orderBy('fecha', 'ASC')
            ->get();

        $resultsBloque        = [];
        $totalTodosLosBloques = 0;

        foreach ($arraySalidas as $salida) {
            $detalleSalida      = SalidasDetalle::where('id_salida', $salida->id)->get();
            $salida->fechaFormat = date("d-m-Y", strtotime($salida->fecha));
            $sumaBloquesTotal   = 0;

            $detalleSalida->each(function ($item) use (&$sumaBloquesTotal) {
                $entradaDetalle = EntradasDetalle::find($item->id_entrada_detalle);
                $infoEntrada    = Entradas::find($entradaDetalle->id_entradas);
                $item->lote     = $infoEntrada->lote ?? '—';

                $material   = Materiales::find($entradaDetalle->id_material);
                $marca      = Marca::find($material->id_marca);
                $normativa  = Normativa::find($material->id_normativa);
                $unidad     = UnidadMedida::find($material->id_medida);

                $item->nombreMaterial      = $material->nombre      ?? '—';
                $item->nombreMarca         = $marca->nombre         ?? '—';
                $item->nombreNormativa     = $normativa->nombre     ?? '—';
                $item->nombreUnidadMedida  = $unidad->nombre        ?? '—';

                $multiplicado      = $item->cantidad_salida * $entradaDetalle->precio;
                $sumaBloquesTotal += $multiplicado;

                $item->precio      = '$' . number_format($entradaDetalle->precio, 2, '.', ',');
                $item->multiplicado = '$' . number_format($multiplicado,          2, '.', ',');
            });

            $totalTodosLosBloques  += $sumaBloquesTotal;
            $salida->sumaBloquesTotal = '$' . number_format($sumaBloquesTotal, 2, '.', ',');
            $salida->detalle          = $detalleSalida->sortBy('nombreMaterial')->values();
            $resultsBloque[]          = $salida;
        }

        $totalTodosLosBloques = '$' . number_format($totalTodosLosBloques, 2, '.', ',');

        // ── Configuración mPDF ─────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Entregas');
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/logo.png';
        $fechaFormat  = date("d-m-Y", strtotime(Carbon::now('America/El_Salvador')));

        // ══ ENCABEZADO ══════════════════════════════════════════════════
        $tabla = "
    <table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif;'>
        <tr>
            <td style='width:25%; border:0.8px solid #000; padding:6px 8px;'>
                <table width='100%'>
                    <tr>
                        <td style='width:30%; text-align:left;'>
                            <img src='{$logoalcaldia}' style='height:38px'>
                        </td>
                        <td style='width:70%; text-align:left; color:#104e8c; font-size:13px; font-weight:bold; line-height:1.3;'>
                            SANTA ANA NORTE<br>EL SALVADOR
                        </td>
                    </tr>
                </table>
            </td>
            <td style='width:50%; border-top:0.8px solid #000; border-bottom:0.8px solid #000;
                        padding:6px 8px; text-align:center; font-size:15px; font-weight:bold;'>
                FICHA DE ENTREGA DE EQUIPO DE<br>PROTECCION PERSONAL (E.P.P)
            </td>
            <td style='width:25%; border:0.8px solid #000; padding:0; vertical-align:top;'>
                <table width='100%' style='font-size:10px;'>
                    <tr>
                        <td width='40%' style='border-right:0.8px solid #000; border-bottom:0.8px solid #000; padding:4px 6px;'><strong>Código:</strong></td>
                        <td width='60%' style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'>SEAC-002-FICH</td>
                    </tr>
                    <tr>
                        <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000; padding:4px 6px;'><strong>Versión:</strong></td>
                        <td style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'>000</td>
                    </tr>
                    <tr>
                        <td style='border-right:0.8px solid #000; padding:4px 6px;'><strong>Fecha de vigencia:</strong></td>
                        <td style='padding:4px 6px; text-align:center;'>22/10/2025</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    ";

        // ══ DATOS DEL EMPLEADO ═══════════════════════════════════════════
        $tabla .= "
    <div style='text-align:center; margin-top:20px;'>
        <h1 style='font-size:15px; margin:0; color:#000;'>ENTREGAS DE MATERIAL</h1>
    </div>
    <div style='text-align:left; margin-top:10px;'>
        <p style='font-size:13px; margin:0;'><strong>Fecha:</strong> {$fechaFormat}</p>
    </div>
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:13px; margin:0;'><strong>Distrito:</strong> {$infoDistrito->nombre}</p>
    </div>
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:13px; margin:0;'><strong>Unidad:</strong> {$infoUnidadEmpleado->nombre}</p>
    </div>
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:13px; margin:0;'><strong>Empleado:</strong> {$infoEmpleado->nombre}</p>
    </div>
    ";

        // ── Sin salidas ────────────────────────────────────────────────
        if (empty($resultsBloque)) {
            $tabla .= "
        <div style='text-align:center; margin-top:40px;'>
            <p style='font-size:13px; color:#888;'>No se encontraron entregas registradas para este empleado.</p>
        </div>
        ";
        }

        // ══ BLOQUES POR SALIDA ════════════════════════════════════════════
        foreach ($resultsBloque as $fila) {

            // — Cabecera del bloque: fecha + descripción —
            $tabla .= "
        <table width='100%' style='margin-top:28px; border-collapse:collapse; font-family:Arial, sans-serif;'>
            <thead>
                <tr>
                    <th style='text-align:center; font-size:11px; width:15%; font-weight:bold;
                                border:1px solid #000; background:#e8eef8; padding:5px;'>Fecha Salida</th>
                    <th style='text-align:center; font-size:11px; width:85%; font-weight:bold;
                                border:1px solid #000; background:#e8eef8; padding:5px;'>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:center;'>
                        {$fila->fechaFormat}
                    </td>
                    <td style='font-size:11px; border:1px solid #000; padding:5px;'>
                        {$fila->descripcion}
                    </td>
                </tr>
            </tbody>
        </table>
        ";

            // — Detalle del bloque —
            $tabla .= "
        <table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif;'>
            <thead>
                <tr>
                    <th style='font-weight:bold; width:11%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Factura</th>
                    <th style='font-weight:bold; width:22%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Material</th>
                    <th style='font-weight:bold; width:13%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Marca</th>
                    <th style='font-weight:bold; width:10%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>U/M</th>
                    <th style='font-weight:bold; width:12%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Normativa</th>
                    <th style='font-weight:bold; width:10%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Cantidad</th>
                    <th style='font-weight:bold; width:11%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Precio</th>
                    <th style='font-weight:bold; width:11%; font-size:11px; text-align:center;
                                border:1px solid #000; background:#f4f6fb; padding:5px;'>Total</th>
                </tr>
            </thead>
            <tbody>
        ";

            foreach ($fila->detalle as $filaDeta) {
                $tabla .= "
            <tr>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:center;'>{$filaDeta->lote}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px;'>{$filaDeta->nombreMaterial}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px;'>{$filaDeta->nombreMarca}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:center;'>{$filaDeta->nombreUnidadMedida}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:center;'>{$filaDeta->nombreNormativa}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:center;'>{$filaDeta->cantidad_salida}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:right;'>{$filaDeta->precio}</td>
                <td style='font-size:11px; border:1px solid #000; padding:5px; text-align:right;'>{$filaDeta->multiplicado}</td>
            </tr>
            ";
            }

            // — Subtotal del bloque —
            $tabla .= "
            <tr>
                <td colspan='7' style='font-size:11px; border:1px solid #000; padding:5px;
                                        text-align:right; font-weight:bold; background:#f9f9f9;'>
                    Subtotal:
                </td>
                <td style='font-size:11px; border:1px solid #000; padding:5px;
                            text-align:right; font-weight:bold; background:#f9f9f9;'>
                    {$fila->sumaBloquesTotal}
                </td>
            </tr>
            </tbody>
        </table>
        ";
        }

        // ══ TOTAL GENERAL ════════════════════════════════════════════════
        $tabla .= "
    <div style='text-align:right; margin-top:20px; margin-right:4px;'>
        <p style='font-size:14px; margin:0; color:#000;'>
            <strong>Total General: {$totalTodosLosBloques}</strong>
        </p>
    </div>
    ";

        // ══ GENERAR PDF ══════════════════════════════════════════════════
        $stylesheet = file_get_contents('css/cssbodega.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->setFooter('Página: {PAGENO}/{nb}');
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }




    public function reportePdfExistencias()
    {
        $mpdf = new \Mpdf\Mpdf([
            'tempDir'      => sys_get_temp_dir(),
            'format'       => 'LETTER',
            'orientation'  => 'P',
            'default_font' => 'arial',
        ]);

        $mpdf->SetTitle('Existencias');
        $mpdf->showImageErrors = false;

        $logoalcaldia = public_path('images/logo.png');
        $fechaFormat  = \Carbon\Carbon::now('America/El_Salvador')->format('d-m-Y');

        // ── Existencias (cantidades) + Obj. Específico ──────────────────
        $existencias = DB::table('materiales as m')
            ->leftJoin('unidadmedida as um', 'um.id', '=', 'm.id_medida')
            ->leftJoin('objeto_especifico as oe', 'oe.id', '=', 'm.id_objespecifico')
            ->leftJoinSub(
                DB::table('entradas_detalle')
                    ->select('id_material', DB::raw('SUM(cantidad_inicial) as total_ingresado'))
                    ->groupBy('id_material'),
                'ed', 'ed.id_material', '=', 'm.id'
            )
            ->leftJoinSub(
                DB::table('salidas_detalle as sd')
                    ->join('entradas_detalle as ed2', 'ed2.id', '=', 'sd.id_entrada_detalle')
                    ->select('ed2.id_material', DB::raw('SUM(sd.cantidad_salida) as total_salido'))
                    ->groupBy('ed2.id_material'),
                'sd', 'sd.id_material', '=', 'm.id'
            )
            ->select(
                'm.id',
                'm.codigo',
                'm.nombre as material',
                'um.nombre as unidad',
                'oe.codigo as oe_codigo',
                'oe.nombre as oe_nombre',
                DB::raw('COALESCE(ed.total_ingresado, 0) as total_ingresado'),
                DB::raw('COALESCE(sd.total_salido, 0) as total_salido'),
                DB::raw('(COALESCE(ed.total_ingresado, 0) - COALESCE(sd.total_salido, 0)) as existencia')
            )
            ->havingRaw('existencia > 0')
            ->orderBy('oe.nombre')
            ->orderBy('m.nombre')
            ->get();

        // ── Precio unitario promedio ponderado por material ──────────────
        $valoresPorMaterial = DB::table('entradas_detalle as ed')
            ->leftJoin(
                DB::raw('(SELECT id_entrada_detalle, SUM(cantidad_salida) as salido
              FROM salidas_detalle
              GROUP BY id_entrada_detalle) as sd'),
                'sd.id_entrada_detalle', '=', 'ed.id'
            )
            ->select(
                'ed.id_material',
                DB::raw('SUM((ed.cantidad_inicial - COALESCE(sd.salido, 0)) * ed.precio) as valor_real')
            )
            ->groupBy('ed.id_material')
            ->pluck('valor_real', 'id_material');

        // ══ ENCABEZADO ═══════════════════════════════════════════════════
        $tabla = "
<table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif; margin-bottom:6px;'>
    <tr>
        <td style='width:22%; border:0.8px solid #000; padding:6px 8px;'>
            <table width='100%'>
                <tr>
                    <td style='width:35%; text-align:left;'>
                        <img src='{$logoalcaldia}' style='height:36px'>
                    </td>
                    <td style='width:65%; text-align:left; color:#000000;
                                font-size:10px; font-weight:bold; line-height:1.3;'>
                        SANTA ANA NORTE<br>EL SALVADOR
                    </td>
                </tr>
            </table>
        </td>
        <td style='width:56%; border-top:0.8px solid #000; border-bottom:0.8px solid #000;
                    padding:8px; text-align:center; vertical-align:middle;'>
            <div style='font-size:14px; font-weight:bold; color:#000000; letter-spacing:1px;'>
                REPORTE DE EXISTENCIAS DE E.P.P.
            </div>
            <div style='font-size:10px; color:#000000; margin-top:3px;'>
                Equipo de Protección Personal — Fecha: <strong>{$fechaFormat}</strong>
            </div>
        </td>
        <td style='width:22%; border:0.8px solid #000; padding:0; vertical-align:top;'>
            <table width='100%' style='font-size:9px; border-collapse:collapse;'>
                <tr>
                    <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000;
                                padding:3px 5px; font-weight:bold; color:#000000;'>Código:</td>
                    <td style='border-bottom:0.8px solid #000; padding:3px 5px;
                                text-align:center; color:#000000;'>
                        SEAC-002-FICH
                    </td>
                </tr>
                <tr>
                    <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000;
                                padding:3px 5px; font-weight:bold; color:#000000;'>Versión:</td>
                    <td style='border-bottom:0.8px solid #000; padding:3px 5px;
                                text-align:center; color:#000000;'>
                        000
                    </td>
                </tr>
                <tr>
                    <td style='border-right:0.8px solid #000; padding:3px 5px;
                                font-weight:bold; color:#000000;'>Vigencia:</td>
                    <td style='padding:3px 5px; text-align:center; color:#000000;'>22/10/2025</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
";

        // ── Sin datos ─────────────────────────────────────────────────────
        if ($existencias->isEmpty()) {
            $tabla .= "
<div style='text-align:center; margin-top:60px; font-family:Arial, sans-serif;'>
    <p style='font-size:13px; color:#888;'>No se encontraron existencias disponibles.</p>
</div>
";
        } else {

            // ── Agrupar por Objeto Específico ─────────────────────────────
            $agrupado = $existencias->groupBy(function ($item) {
                return $item->oe_codigo . '|' . $item->oe_nombre;
            });

            $tabla .= "
<table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif; margin-top:8px;'>
    <thead>
        <tr>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:4%;'>#</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:9%;'>Código</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:30%;'>Material</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:14%;'>Obj. Específico</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:9%;'>Unidad</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:9%;'>Existencia</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:12%;'>Precio Unit. ($)</th>
            <th style='background:#f2f4f8; color:#000; font-size:9px; font-weight:bold;
                        border:1px solid #000; padding:5px 3px; text-align:center; width:13%;'>Total ($)</th>
        </tr>
    </thead>
    <tbody>
";

            $cont         = 1;
            $totalGeneral = 0;

            foreach ($agrupado as $grupoKey => $items) {

                [$oeCodigo, $oeNombre] = explode('|', $grupoKey, 2);
                $oeLabel = trim($oeCodigo) !== ''
                    ? "{$oeCodigo} - {$oeNombre}"
                    : ($oeNombre ?: 'SIN OBJETO ESPECÍFICO');

                $subtotalGrupo = 0;

                foreach ($items as $item) {
                    $existencia    = (int)   $item->existencia;
                    $valorReal     = (float) ($valoresPorMaterial[$item->id] ?? 0);
                    $precioUnit    = $existencia > 0 ? ($valorReal / $existencia) : 0;
                    $totalItem     = $valorReal;
                    $subtotalGrupo += $totalItem;
                    $totalGeneral  += $totalItem;

                    $tabla .= "
        <tr>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:center;'>{$cont}</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:center;'>{$item->codigo}</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px;'>{$item->material}</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:center;'>" . ($item->oe_codigo ?? '—') . "</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:center;'>" . ($item->unidad ?? '—') . "</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:center;'>{$existencia}</td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:right;'>
                \$ " . number_format($precioUnit, 2) . "
            </td>
            <td style='border:1px solid #000; font-size:9px; padding:4px; text-align:right;'>
                \$ " . number_format($totalItem, 2) . "
            </td>
        </tr>
        ";
                    $cont++;
                }

                // ── Subtotal por Objeto Específico ──
                $tabla .= "
        <tr>
            <td colspan='7' style='border:1px solid #000; padding:5px 8px; text-align:right;
                                    font-size:9px; font-weight:bold;
                                    background:#f2f4f8; color:#000;'>
                SUBTOTAL — {$oeLabel}
            </td>
            <td style='border:1px solid #000; padding:5px; text-align:right;
                        font-size:9px; font-weight:bold;
                        background:#f2f4f8; color:#000;'>
                \$ " . number_format($subtotalGrupo, 2) . "
            </td>
        </tr>
        ";
            }

            $tabla .= "
    </tbody>
    <tfoot>
        <tr>
            <td colspan='7' style='border:1px solid #000; padding:7px 8px; text-align:right;
                                    font-size:11px; font-weight:bold;
                                    background:#f9fafb; color:#000; letter-spacing:.5px;'>
                TOTAL GENERAL
            </td>
            <td style='border:1px solid #000; padding:7px; text-align:right;
                        font-size:12px; font-weight:bold;
                        background:#f9fafb; color:#000;'>
                \$ " . number_format($totalGeneral, 2) . "
            </td>
        </tr>
    </tfoot>
</table>
";
        }

        // ── Generar PDF ────────────────────────────────────────────────
        $stylesheet = file_get_contents(public_path('css/cssbodega.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->setFooter('Página: {PAGENO}/{nb}');
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }


    public function reportePDFInicialPorPeriodos($desde, $hasta)
    {
        $start = Carbon::parse($desde)->startOfDay();
        $end   = Carbon::parse($hasta)->endOfDay();

        $desdeFormat = Carbon::parse($desde)->format('d/m/Y');
        $hastaFormat = Carbon::parse($hasta)->format('d/m/Y');

        $rows = DB::select("
    WITH movimientos AS (
        SELECT
            ed.id_material,
            COALESCE(NULLIF(oe.codigo, ''), 'SIN-CODIGO') AS codigo,
            m.nombre AS descripcion,
            ed.precio,
            e.fecha AS fecha_movimiento,
            ed.cantidad_inicial AS entrada,
            0 AS salida
        FROM entradas_detalle ed
        INNER JOIN entradas e   ON e.id  = ed.id_entradas AND e.id_bodega = 2
        INNER JOIN materiales m ON m.id  = ed.id_material
        LEFT  JOIN objeto_especifico oe ON oe.id = m.id_objespecifico

        UNION ALL

        SELECT
            ed.id_material,
            COALESCE(NULLIF(oe.codigo, ''), 'SIN-CODIGO') AS codigo,
            m.nombre AS descripcion,
            ed.precio,
            s.fecha AS fecha_movimiento,
            0 AS entrada,
            sd.cantidad_salida AS salida
        FROM salidas_detalle sd
        INNER JOIN salidas s            ON s.id  = sd.id_salida
        INNER JOIN entradas_detalle ed  ON ed.id = sd.id_entrada_detalle
        INNER JOIN entradas e           ON e.id  = ed.id_entradas AND e.id_bodega = 2
        INNER JOIN materiales m         ON m.id  = ed.id_material
        LEFT  JOIN objeto_especifico oe ON oe.id = m.id_objespecifico
    )
    SELECT
        id_material, codigo, descripcion,
        MAX(precio) AS precio,

        SUM(CASE WHEN fecha_movimiento <  ? THEN entrada - salida ELSE 0 END) AS saldo_inicial_cant,
        SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN entrada ELSE 0 END) AS entradas_mes_cant,
        SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN salida  ELSE 0 END) AS salidas_mes_cant,

        (
            SUM(CASE WHEN fecha_movimiento <  ? THEN entrada - salida ELSE 0 END)
          + SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN entrada ELSE 0 END)
          - SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN salida  ELSE 0 END)
        ) AS saldo_final_cant,

        SUM(CASE WHEN fecha_movimiento <  ? THEN entrada - salida ELSE 0 END) * MAX(precio) AS saldo_inicial_money,
        SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN entrada ELSE 0 END) * MAX(precio) AS entradas_mes_money,
        SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN salida  ELSE 0 END) * MAX(precio) AS salidas_mes_money,

        (
            SUM(CASE WHEN fecha_movimiento <  ? THEN entrada - salida ELSE 0 END)
          + SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN entrada ELSE 0 END)
          - SUM(CASE WHEN fecha_movimiento >= ? AND fecha_movimiento <= ? THEN salida  ELSE 0 END)
        ) * MAX(precio) AS saldo_final_money

            FROM movimientos
            GROUP BY id_material, codigo, descripcion
            ORDER BY codigo, descripcion
        ", [
            $start->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(),
            $start->toDateString(), $end->toDateString(),
            $start->toDateString(), $end->toDateString(),
        ]);

        // ── Filtrar filas completamente en cero ───────────────────────────────────
        $rows = array_values(array_filter($rows, function ($r) {
            return !((float)($r->saldo_inicial_cant ?? 0) == 0
                && (float)($r->entradas_mes_cant  ?? 0) == 0
                && (float)($r->salidas_mes_cant   ?? 0) == 0
                && (float)($r->saldo_final_cant   ?? 0) == 0);
        }));

        // ── Totales y agrupado por código ─────────────────────────────────────────
        $totales = [
            'inicial_cant'   => 0, 'inicial_money'  => 0.0,
            'entradas_cant'  => 0, 'entradas_money' => 0.0,
            'salidas_cant'   => 0, 'salidas_money'  => 0.0,
            'final_cant'     => 0, 'final_money'    => 0.0,
        ];
        $sumPorCodigo = [];

        foreach ($rows as $r) {
            $totales['inicial_cant']   += (int)   ($r->saldo_inicial_cant  ?? 0);
            $totales['entradas_cant']  += (int)   ($r->entradas_mes_cant   ?? 0);
            $totales['salidas_cant']   += (int)   ($r->salidas_mes_cant    ?? 0);
            $totales['final_cant']     += (int)   ($r->saldo_final_cant    ?? 0);
            $totales['inicial_money']  += (float) ($r->saldo_inicial_money ?? 0);
            $totales['entradas_money'] += (float) ($r->entradas_mes_money  ?? 0);
            $totales['salidas_money']  += (float) ($r->salidas_mes_money   ?? 0);
            $totales['final_money']    += (float) ($r->saldo_final_money   ?? 0);

            $cod = $r->codigo ?? 'SIN-CODIGO';
            if (!isset($sumPorCodigo[$cod])) {
                $sumPorCodigo[$cod] = [
                    'codigo'        => $cod,
                    'inicial_cant'  => 0, 'inicial_money'  => 0.0,
                    'entradas_cant' => 0, 'entradas_money' => 0.0,
                    'salidas_cant'  => 0, 'salidas_money'  => 0.0,
                    'final_cant'    => 0, 'final_money'    => 0.0,
                ];
            }
            $sumPorCodigo[$cod]['inicial_cant']   += (int)   ($r->saldo_inicial_cant  ?? 0);
            $sumPorCodigo[$cod]['entradas_cant']  += (int)   ($r->entradas_mes_cant   ?? 0);
            $sumPorCodigo[$cod]['salidas_cant']   += (int)   ($r->salidas_mes_cant    ?? 0);
            $sumPorCodigo[$cod]['final_cant']     += (int)   ($r->saldo_final_cant    ?? 0);
            $sumPorCodigo[$cod]['inicial_money']  += (float) ($r->saldo_inicial_money ?? 0);
            $sumPorCodigo[$cod]['entradas_money'] += (float) ($r->entradas_mes_money  ?? 0);
            $sumPorCodigo[$cod]['salidas_money']  += (float) ($r->salidas_mes_money   ?? 0);
            $sumPorCodigo[$cod]['final_money']    += (float) ($r->saldo_final_money   ?? 0);
        }

        // ── PDF ───────────────────────────────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf([
            'tempDir'      => sys_get_temp_dir(),
            'format'       => 'LETTER',
            'orientation'  => 'L',
            'default_font' => 'arial',
        ]);
        $mpdf->SetTitle('Control de Entradas/Salidas por Período');
        $mpdf->showImageErrors = false;

        $logoalcaldia = public_path('images/gobiernologo.jpg');

        if (file_exists(public_path('css/cssbodega.css'))) {
            $mpdf->WriteHTML(
                file_get_contents(public_path('css/cssbodega.css')),
                \Mpdf\HTMLParserMode::HEADER_CSS
            );
        }

        // ══ ENCABEZADO (igual a la imagen) ═══════════════════════════════════════
        $html = "
<table width='100%' style='border-collapse:collapse; font-family:Arial,sans-serif; margin-bottom:6px;'>
    <tr>
        <td style='width:22%; border:0.8px solid #000; padding:6px 8px;'>
            <table width='100%'>
                <tr>
                    <td style='width:35%; text-align:left;'>
                        <img src='{$logoalcaldia}' style='height:40px'>
                    </td>
                    <td style='width:65%; text-align:left; color:#104e8c; font-size:11px; font-weight:bold; line-height:1.4;'>
                        SANTA ANA NORTE<br>EL SALVADOR
                    </td>
                </tr>
            </table>
        </td>
        <td style='width:56%; border-top:0.8px solid #000; border-bottom:0.8px solid #000;
                    padding:8px; text-align:center; vertical-align:middle;'>
            <div style='font-size:15px; font-weight:bold; color:#000; letter-spacing:.5px;'>
                CONTROL DE ENTRADAS / SALIDAS
            </div>
        </td>
        <td style='width:22%; border:0.8px solid #000; padding:0; vertical-align:top;'>
            <table width='100%' style='font-size:10px; border-collapse:collapse;'>
                <tr>
                    <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000; padding:4px 6px; font-weight:bold;'>Código:</td>
                    <td style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'></td>
                </tr>
                <tr>
                    <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000; padding:4px 6px; font-weight:bold;'>Versión:</td>
                    <td style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'>000</td>
                </tr>
                <tr>
                    <td style='border-right:0.8px solid #000; padding:4px 6px; font-weight:bold;'>Fecha de vigencia:</td>
                    <td style='padding:4px 6px; text-align:center;'></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<strong>Del {$desdeFormat} al {$hastaFormat}</strong><br>
";

        // ══ TABLA DETALLE (sin colores, estilo simple como la imagen) ═════════════
        $html .= "
<table width='100%' border='1' cellspacing='0' cellpadding='4'
       style='border-collapse:collapse; font-family:Arial,sans-serif; font-size:11px; margin-top:8px;'>
    <thead style='background:#f2f4f8;'>
        <tr>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:3%;'>#</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:9%;'>Código</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:20%;'>Descripción / Nombre</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:7%;'>PRECIO</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>INICIAL</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:7%;'>\$ INICIAL</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>ENTRADAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:7%;'>\$ ENTRADAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>SALIDAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:7%;'>\$ SALIDAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>SALDO</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:7%;'>\$ SALDO</th>
        </tr>
    </thead>
    <tbody>
";

        $i = 1;
        foreach ($rows as $r) {
            $html .= "
    <tr>
       <td style='border:1px solid #000; padding:4px; text-align:center;'>{$i}</td>
        <td style='border:1px solid #000; padding:4px; text-align:center;'>" . e($r->codigo ?? '—') . "</td>
        <td style='border:1px solid #000; padding:4px;'>" . e($r->descripcion ?? '—') . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($r->precio ?? 0, 4) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($r->saldo_inicial_cant ?? 0) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($r->saldo_inicial_money ?? 0, 2) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($r->entradas_mes_cant ?? 0) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($r->entradas_mes_money ?? 0, 2) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($r->salidas_mes_cant ?? 0) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($r->salidas_mes_money ?? 0, 2) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($r->saldo_final_cant ?? 0) . "</td>
        <td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($r->saldo_final_money ?? 0, 2) . "</td>
    </tr>
    ";
            $i++;
        }

        if (empty($rows)) {
            $html .= "<tr><td colspan='12' style='text-align:center; color:#888; padding:12px;'>Sin registros en el rango seleccionado.</td></tr>";
        }

        // ── Fila totales ──────────────────────────────────────────────────────────
        $html .= "
    </tbody>
    <tfoot>
        <tr style='font-weight:bold; background:#f9fafb;'>
            <td colspan='4' style='border:1px solid #000; padding:5px 8px; text-align:right;'>Totales:</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>" . number_format($totales['inicial_cant']) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>\$" . number_format($totales['inicial_money'], 2) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>" . number_format($totales['entradas_cant']) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>\$" . number_format($totales['entradas_money'], 2) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>" . number_format($totales['salidas_cant']) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>\$" . number_format($totales['salidas_money'], 2) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>" . number_format($totales['final_cant']) . "</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>\$" . number_format($totales['final_money'], 2) . "</td>
        </tr>
    </tfoot>
</table>
";

        // ══ RESUMEN DEL PERÍODO (sin colores) ════════════════════════════════════
        $html .= "
<br>
<table width='60%' border='1' cellspacing='0' cellpadding='6'
       style='border-collapse:collapse; font-family:Arial,sans-serif; font-size:12px;'>
    <tr style='background:#eef3ff; font-weight:bold; text-align:center;'>
        <td colspan='3'>Resumen del período {$desdeFormat} - {$hastaFormat}</td>
    </tr>
    <tr style='font-weight:bold; background:#f9fafb;'>
        <td></td>
        <td style='text-align:right;'>Cantidad</td>
        <td style='text-align:right;'>Dinero ($)</td>
    </tr>
    <tr>
        <td>Ingresó (Entradas del mes)</td>
        <td style='text-align:right;'>" . number_format($totales['entradas_cant']) . "</td>
        <td style='text-align:right;'>\$" . number_format($totales['entradas_money'], 2) . "</td>
    </tr>
    <tr>
        <td>Salió (Salidas del mes)</td>
        <td style='text-align:right;'>" . number_format($totales['salidas_cant']) . "</td>
        <td style='text-align:right;'>\$" . number_format($totales['salidas_money'], 2) . "</td>
    </tr>
    <tr>
        <td>Disponible al cierre (Saldo final)</td>
        <td style='text-align:right;'>" . number_format($totales['final_cant']) . "</td>
        <td style='text-align:right;'>\$" . number_format($totales['final_money'], 2) . "</td>
    </tr>
</table>
";

        // ══ RESUMEN POR OBJETO ESPECÍFICO (sin colores) ═══════════════════════════
        if (!empty($sumPorCodigo)) {
            $totalSaldoFinalCodigos = 0;

            $html .= "
<br><br>
<table width='100%' border='1' cellspacing='0' cellpadding='4'
       style='border-collapse:collapse; font-family:Arial,sans-serif; font-size:11px;'>
    <thead style='background:#f2f4f8;'>
        <tr>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:4%;'>#</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:10%;'>Código</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>INICIAL</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:10%;'>\$ INICIAL</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>ENTRADAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:10%;'>\$ ENTRADAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>SALIDAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:10%;'>\$ SALIDAS</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:center; width:6%;'>SALDO</th>
            <th style='border:1px solid #000; padding:5px 3px; text-align:right;  width:10%;'>\$ SALDO</th>
        </tr>
    </thead>
    <tbody>
";

            $j = 1;
            foreach ($sumPorCodigo as $s) {
                $totalSaldoFinalCodigos += (float) $s['final_money'];
                $html .= "
    <tr>
      <td style='border:1px solid #000; padding:4px; text-align:center;'>{$j}</td>
<td style='border:1px solid #000; padding:4px; text-align:center;'>" . e($s['codigo']) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($s['inicial_cant']) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($s['inicial_money'], 2) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($s['entradas_cant']) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($s['entradas_money'], 2) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($s['salidas_cant']) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($s['salidas_money'], 2) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>" . number_format($s['final_cant']) . "</td>
<td style='border:1px solid #000; padding:4px; text-align:right;'>\$" . number_format($s['final_money'], 2) . "</td>
    </tr>
    ";
                $j++;
            }

            $html .= "
    </tbody>
    <tfoot>
        <tr style='font-weight:bold; background:#f9fafb;'>
            <td colspan='9' style='border:1px solid #000; padding:5px 8px; text-align:right;'>TOTAL</td>
            <td style='border:1px solid #000; padding:5px; text-align:right;'>\$" . number_format($totalSaldoFinalCodigos, 2) . "</td>
        </tr>
    </tfoot>
</table>
";
        }

        // ══ FIRMA ═════════════════════════════════════════════════════════════════
        $html .= "
        <table width='100%' style='border-collapse:collapse;'>
            <tr>
                <td style='height:60px;'></td>
            </tr>
        </table>
        <table width='100%' style='border-collapse:collapse;'>
            <tr>
                <td style='text-align:center; font-family:Arial,sans-serif; font-size:13px;'>
                    F._____________________________
                </td>
            </tr>
            <tr>
                <td style='height:6px;'></td>
            </tr>
            <tr>
                <td style='text-align:center; font-family:Arial,sans-serif; font-size:12px; font-weight:bold;'>
                    UNIDAD DE SEGURIDAD Y SALUD OCUPACIONAL
                </td>
            </tr>
        </table>
        ";

        $mpdf->setFooter('Página {PAGENO} de {nb}');
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }


}
