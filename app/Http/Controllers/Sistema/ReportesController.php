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
        \Log::info('id recibido:', $request->all());

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
            'orientation'  => 'L',
            'default_font' => 'arial',
        ]);

        $mpdf->SetTitle('Existencias');
        $mpdf->showImageErrors = false;

        $logoalcaldia = public_path('images/logo.png');
        $fechaFormat  = \Carbon\Carbon::now('America/El_Salvador')->format('d-m-Y');

        // ── Existencias (cantidades) ───────────────────────────────────
        $existencias = DB::table('materiales as m')
            ->leftJoin('unidadmedida as um', 'um.id', '=', 'm.id_medida')
            ->leftJoin('marca as ma', 'ma.id', '=', 'm.id_marca')
            ->leftJoin('color as c', 'c.id', '=', 'm.id_color')
            ->leftJoin('talla as t', 't.id', '=', 'm.id_talla')
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
                'ma.nombre as marca',
                'c.nombre as color',
                't.nombre as talla',
                DB::raw('COALESCE(ed.total_ingresado, 0) as total_ingresado'),
                DB::raw('COALESCE(sd.total_salido, 0) as total_salido'),
                DB::raw('(COALESCE(ed.total_ingresado, 0) - COALESCE(sd.total_salido, 0)) as existencia')
            )
            ->havingRaw('existencia > 0')
            ->orderBy('m.nombre')
            ->get();

        // ── Valor correcto por lote: (cantidad_inicial - salido) × precio ──
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

        // ══ ENCABEZADO ═════════════════════════════════════════════════
        $tabla = "
    <table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif; margin-bottom:6px;'>
        <tr>
            <td style='width:20%; border:0.8px solid #000; padding:6px 8px;'>
                <table width='100%'>
                    <tr>
                        <td style='width:35%; text-align:left;'>
                            <img src='{$logoalcaldia}' style='height:40px'>
                        </td>
                        <td style='width:65%; text-align:left; color:#104e8c;
                                    font-size:12px; font-weight:bold; line-height:1.4;'>
                            SANTA ANA NORTE<br>EL SALVADOR
                        </td>
                    </tr>
                </table>
            </td>
            <td style='width:60%; border-top:0.8px solid #000; border-bottom:0.8px solid #000;
                        padding:8px; text-align:center; vertical-align:middle;'>
                <div style='font-size:16px; font-weight:bold; color:#1a3a6b; letter-spacing:1px;'>
                    REPORTE DE EXISTENCIAS DE E.P.P.
                </div>
                <div style='font-size:11px; color:#555; margin-top:3px;'>
                    Equipo de Protección Personal — Fecha: <strong>{$fechaFormat}</strong>
                </div>
            </td>
            <td style='width:20%; border:0.8px solid #000; padding:0; vertical-align:top;'>
                <table width='100%' style='font-size:10px; border-collapse:collapse;'>
                    <tr>
                        <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000;
                                    padding:4px 6px; font-weight:bold;'>Código:</td>
                        <td style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'>
                            SEAC-002-FICH
                        </td>
                    </tr>
                    <tr>
                        <td style='border-right:0.8px solid #000; border-bottom:0.8px solid #000;
                                    padding:4px 6px; font-weight:bold;'>Versión:</td>
                        <td style='border-bottom:0.8px solid #000; padding:4px 6px; text-align:center;'>
                            000
                        </td>
                    </tr>
                    <tr>
                        <td style='border-right:0.8px solid #000; padding:4px 6px; font-weight:bold;'>
                            Vigencia:
                        </td>
                        <td style='padding:4px 6px; text-align:center;'>22/10/2025</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    ";

        // ── Sin datos ──────────────────────────────────────────────────
        if ($existencias->isEmpty()) {
            $tabla .= "
        <div style='text-align:center; margin-top:60px; font-family:Arial, sans-serif;'>
            <p style='font-size:13px; color:#888;'>No se encontraron existencias disponibles.</p>
        </div>
        ";
        } else {

            // ══ TABLA ══════════════════════════════════════════════════
            $tabla .= "
        <table width='100%' style='border-collapse:collapse; font-family:Arial, sans-serif; margin-top:10px;'>
            <thead>
                <tr>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:3%;'>#</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:8%;'>Código</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:28%;'>Material</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:12%;'>Marca</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:9%;'>Color</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:8%;'>Talla</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:9%;'>Unidad</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:10%;'>Existencia</th>
                    <th style='background:#1a3a6b; color:#fff; font-size:10px; font-weight:bold;
                                border:1px solid #000; padding:6px 4px; text-align:center; width:13%;'>Valor ($)</th>
                </tr>
            </thead>
            <tbody>
        ";

            $cont       = 1;
            $totalValor = 0;

            foreach ($existencias as $item) {
                $existencia  = (int)   $item->existencia;
                $valor       = (float) ($valoresPorMaterial[$item->id] ?? 0);
                $totalValor += $valor;

                $tabla .= "
            <tr>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center; color:#666;'>{$cont}</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center; font-weight:bold; color:#1a3a6b;'>{$item->codigo}</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            font-weight:600; color:#1a2d55;'>{$item->material}</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center;'>" . ($item->marca  ?? '—') . "</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center;'>" . ($item->color  ?? '—') . "</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center;'>" . ($item->talla  ?? '—') . "</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center;'>" . ($item->unidad ?? '—') . "</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px;
                            text-align:center;'>{$existencia}</td>
                <td style='border:1px solid #ccc; font-size:10px; padding:5px; text-align:right;'>
                    \$ " . number_format($valor, 2) . "
                </td>
            </tr>
            ";

                $cont++;
            }

            $tabla .= "
            </tbody>
            <tfoot>
                <tr>
                    <td colspan='8' style='border:1px solid #000; padding:7px 8px; text-align:right;
                                            font-size:11px; font-weight:bold;
                                            background:#1a3a6b; color:#fff; letter-spacing:.5px;'>
                        TOTAL GENERAL
                    </td>
                    <td style='border:1px solid #000; padding:7px; text-align:right;
                                font-size:12px; font-weight:bold;
                                background:#1a3a6b; color:#fff;'>
                        \$ " . number_format($totalValor, 2) . "
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









}
