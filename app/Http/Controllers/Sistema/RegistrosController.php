<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Color;
use App\Models\Distrito;
use App\Models\Empleado;
use App\Models\Entradas;
use App\Models\EntradasDetalle;
use App\Models\JefeFirma;
use App\Models\Marca;
use App\Models\Materiales;
use App\Models\Normativa;
use App\Models\ObjetoEspecifico;
use App\Models\Proveedor;
use App\Models\SalidaDetalleTemporal;
use App\Models\Salidas;
use App\Models\SalidasDetalle;
use App\Models\SalidaTemporal;
use App\Models\Talla;
use App\Models\UnidadEmpleado;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegistrosController extends Controller
{

    public function indexRegistroEntrada(){

        $arrayProveedor = Proveedor::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.registros.entradas.vistaentradasregistros', compact('arrayProveedor'));
    }

    public function buscadorMaterialGlobal(Request $request){

        if($request->get('query')){
            $query = $request->get('query');
            $arrayMateriales = Materiales::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo', 'LIKE', "%{$query}%")
                ->get();

            $output = '<ul class="dropdown-menu" style="display:block; position:relative; overflow: auto; ">';
            $tiene = true;
            foreach($arrayMateriales as $row){

                $medida = "";
                $marca = "";
                $normativa = "";
                $color = "";
                $talla = "";

                if($info = UnidadMedida::where('id', $row->id_medida)->first()){
                    $medida = "(" . $info->nombre . ")";
                }

                if($info = Marca::where('id', $row->id_marca)->first()){
                    $marca = "(" . $info->nombre . ")";
                }

                if($info = Normativa::where('id', $row->id_normativa)->first()){
                    $normativa = "(" . $info->nombre . ")";
                }

                if($info = Color::where('id', $row->id_color)->first()){
                    $color = "(" . $info->nombre . ")";
                }

                if($info = Talla::where('id', $row->id_talla)->first()){
                    $talla = "(" . $info->nombre . ")";
                }



                $nombreCompleto = $row->nombre . '  ' .$medida . '  ' .$marca . '  ' .$normativa . '  ' .$color . '  ' .$talla;


                // si solo hay 1 fila, No mostrara el hr, salto de linea
                if(count($arrayMateriales) == 1){
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px; color: black">'.$nombreCompleto .'</a></li>
                ';
                    }
                }

                else{
                    if(!empty($row)){
                        $tiene = false;
                        $output .= '
                 <li class="cursor-pointer" onclick="modificarValor(this)" id="'.$row->id.'"><a href="#" style="margin-left: 3px; color: black">'.$nombreCompleto .'</a></li>
                   <hr>
                ';
                    }
                }
            }
            $output .= '</ul>';
            if($tiene){
                $output = '';
            }
            echo $output;
        }
    }

    // GUARDAR ENTRADAS
    public function guardarEntrada(Request $request)
    {
        $rules = [
            'fecha'     => 'required',
            'proveedor' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return ['success' => 0];
        }

        DB::beginTransaction();

        try {
            $datosContenedor = json_decode($request->contenedorArray, true);

            // ── Cabecera ──
            $registro = new Entradas();
            $registro->fecha        = $request->fecha;
            $registro->descripcion  = $request->observacion;
            $registro->lote         = $request->lote;
            $registro->id_proveedor = $request->proveedor;
            $registro->id_bodega = 2; // siempre bodega #2
            $registro->save();

            // ── Detalle ──
            foreach ($datosContenedor as $fila) {
                $detalle = new EntradasDetalle();
                $detalle->id_entradas        = $registro->id;
                $detalle->id_material        = $fila['idMaterial'];
                $detalle->cantidad_inicial   = $fila['infoCantidad'];
                $detalle->precio             = $fila['infoPrecio'];
                $detalle->save();
            }

            DB::commit();
            return ['success' => 1];

        } catch (\Throwable $e) {
            Log::error('guardarEntrada: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }

    public function buscarMaterialesPorCodigo(Request $request)
    {
        $query = $request->get('query');

        if (empty($query)) {
            return response()->json([]);
        }

        $arrayMateriales = Materiales::where('codigo', 'LIKE', "%{$query}%")->get();

        $resultado = [];

        foreach ($arrayMateriales as $row) {

            $medida    = "";
            $marca     = "";
            $normativa = "";
            $color     = "";
            $talla     = "";

            if ($info = UnidadMedida::where('id', $row->id_medida)->first()) {
                $medida = "(" . $info->nombre . ")";
            }
            if ($info = Marca::where('id', $row->id_marca)->first()) {
                $marca = "(" . $info->nombre . ")";
            }
            if ($info = Normativa::where('id', $row->id_normativa)->first()) {
                $normativa = "(" . $info->nombre . ")";
            }
            if ($info = Color::where('id', $row->id_color)->first()) {
                $color = "(" . $info->nombre . ")";
            }
            if ($info = Talla::where('id', $row->id_talla)->first()) {
                $talla = "(" . $info->nombre . ")";
            }

            $nombreCompleto = trim($row->nombre . '  ' . $medida . '  ' . $marca . '  ' . $normativa . '  ' . $color . '  ' . $talla);

            $resultado[] = [
                'id'     => $row->id,
                'nombre' => $nombreCompleto,
                'codigo' => $row->codigo,
            ];
        }

        return response()->json($resultado);
    }














    // ****************************** SALIDAS *****************************************


    public function indexRegistroSalida(){

        $arrayDistritos = Distrito::orderBy('nombre', 'ASC')->get();
        $arrayJefeFirma = JefeFirma::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.registros.salidas.vistasalidaregistro', compact('arrayDistritos', 'arrayJefeFirma'));
    }

    public function buscadorMaterialDisponible(Request $request)
    {
        if ($request->get('query')) {

            $query = $request->get('query');

            $materiales = Materiales::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('codigo', 'LIKE', "%{$query}%")
                ->pluck('id');

            if ($materiales->isEmpty()) {
                return '';
            }

            // ✅ Agrupar por id_material y SUMAR todos los disponibles
            $listado = DB::table('entradas_detalle as ed')
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
                ->groupBy('ed.id_material')
                ->havingRaw('disponible > 0')
                ->orderBy('ed.id_material')
                ->get();

            if ($listado->isEmpty()) {
                return '';
            }

            $output = '<ul class="dropdown-menu" style="display:block; position:relative; overflow:auto; max-height:300px; width:800px">';

            foreach ($listado as $row) {

                $infoMaterial = Materiales::with(['marca','unidadMedida','normativa','color','talla'])
                    ->find($row->id_material);

                if (!$infoMaterial) continue;

                $nombreCompleto = $infoMaterial->nombre .
                    " (" . optional($infoMaterial->unidadMedida)->nombre . ")" .
                    " (" . optional($infoMaterial->marca)->nombre . ")" .
                    " (" . optional($infoMaterial->normativa)->nombre . ")" .
                    " (" . optional($infoMaterial->color)->nombre . ")" .
                    " (" . optional($infoMaterial->talla)->nombre . ")";

                // ✅ Usar id_material como id del <li> para el onclick
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


    // UTILIZADO PARA LLENAR EL ARRAY DEL MODAL DE UN MATERIAL
    public function infoBodegaMaterialDetalleFila(Request $request)
    {
        $regla = [
            'id' => 'required', // ahora 'id' es el id_material
        ];

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) {
            return ['success' => 0];
        }

        // ✅ Buscar el material directamente por id (ya no por entradas_detalle)
        $infoMaterial = Materiales::find($request->id);
        if (!$infoMaterial) {
            return ['success' => 0];
        }

        $infoMedida    = UnidadMedida::find($infoMaterial->id_medida);
        $infoMarca     = Marca::find($infoMaterial->id_marca);
        $infoNormativa = Normativa::find($infoMaterial->id_normativa);

        $color = "";
        if ($infoMaterial->id_color) {
            $infoColor = Color::find($infoMaterial->id_color);
            $color = $infoColor ? $infoColor->nombre : "";
        }

        $talla = "";
        if ($infoMaterial->id_talla) {
            $infoTalla = Talla::find($infoMaterial->id_talla);
            $talla = $infoTalla ? $infoTalla->nombre : "";
        }

        // ✅ Subquery para evitar multiplicación de filas por múltiples salidas
        $listado = DB::table('entradas_detalle as ed')
            ->leftJoin(
                DB::raw('(
                SELECT id_entrada_detalle, SUM(cantidad_salida) as total_salido
                FROM salidas_detalle
                GROUP BY id_entrada_detalle
            ) as sd'),
                'sd.id_entrada_detalle', '=', 'ed.id'
            )
            ->leftJoin('entradas as e', 'e.id', '=', 'ed.id_entradas')
            ->leftJoin('proveedor as p', 'p.id', '=', 'e.id_proveedor')
            ->select(
                'ed.id',
                'ed.id_entradas',
                'ed.cantidad_inicial',
                'ed.precio',
                'e.lote',
                'e.fecha',
                'p.nombre as proveedor',
                DB::raw('COALESCE(sd.total_salido, 0) as total_salido'),
                DB::raw('(ed.cantidad_inicial - COALESCE(sd.total_salido, 0)) as cantidadActual')
            )
            // ✅ Filtrar directamente por id_material
            ->where('ed.id_material', $request->id)
            ->havingRaw('cantidadActual > 0')
            ->orderBy('ed.id')
            ->get();

        foreach ($listado as $fila) {
            $fila->fechaIngreso   = date("d-m-Y", strtotime($fila->fecha));
            $fila->precioFormat   = '$' . number_format($fila->precio, 2, '.', ',');
            $fila->mesesreemplazo = $infoMaterial->meses_cambio ?? 0;
            $fila->cantidad       = $fila->cantidadActual;

            // ✅ Evitar null en campos opcionales
            $fila->proveedor = $fila->proveedor ?? '';
            $fila->lote      = $fila->lote      ?? '';
        }

        $disponible = $listado->isEmpty() ? 1 : 0;

        return [
            'success'         => 1,
            'nombreMaterial'  => $infoMaterial->nombre ?? '',
            'nombreMarca'     => $infoMarca->nombre ?? '',
            'nombreNormativa' => $infoNormativa->nombre ?? '',
            'nombreMedida'    => $infoMedida->nombre ?? '',
            'nombreColor'     => $color,
            'nombreTalla'     => $talla,
            'arrayIngreso'    => $listado,
            'disponible'      => $disponible,
        ];
    }





    public function guardarSalidaMateriales(Request $request)
    {
        $validar = Validator::make($request->all(), [
            'fecha'    => 'required',
            'empleado' => 'required',
            'jefeFirma' => 'required',
        ]);

        if ($validar->fails()) return ['success' => 0];

        DB::beginTransaction();

        try {

            // ── Info empleado con eager loading (sin N+1) ──────────────
            $infoEmpleado = Empleado::with(['unidadEmpleado', 'cargo'])->findOrFail($request->empleado);
            $infoUnidad   = $infoEmpleado->unidadEmpleado;
            $cargo        = $infoEmpleado->cargo->nombre ?? '—';

            // ── Jefe inmediato (desde jefe_unidad) ──────────────────────────
            if ($infoEmpleado->jefe == 1) {
                // Soy jefe → mi superior viene de id_jefe (autorreferencia)
                $jefeDirecto   = Empleado::find($infoEmpleado->id_jefe);
                $jefeInmediato = $jefeDirecto?->nombre ?? '';
            } else {
                // Soy empleado normal → buscar en jefe_unidad (puede haber varios)
                $jefeInmediato = DB::table('jefe_unidad')
                    ->join('empleado', 'jefe_unidad.id_empleado', '=', 'empleado.id')
                    ->where('jefe_unidad.id_unidad_empleado', $infoEmpleado->id_unidad_empleado)
                    ->pluck('empleado.nombre')
                    ->implode(' / ') ?: '';
            }

            // ── Validar contenedor ─────────────────────────────────────
            $datosContenedor = json_decode($request->contenedorArray, true);

            if (empty($datosContenedor)) {
                DB::rollback();
                return ['success' => 1];
            }

            $infoJefeFirma = JefeFirma::where('id', $request->jefeFirma)->first();

            // ── Crear registro cabecera de salida ──────────────────────
            $reg                 = new Salidas();
            $reg->fecha          = $request->fecha;
            $reg->id_empleado    = $request->empleado;
            $reg->descripcion    = $request->descripcion ?? '';
            $reg->area           = $infoUnidad->nombre   ?? '';
            $reg->cargo          = $cargo;
            $reg->colaborador    = $infoEmpleado->nombre;
            $reg->jefe_inmediato = $jefeInmediato;
            $reg->material_linea = $request->lineaEditar ?? '';
            $reg->jefe_firma     = $infoJefeFirma->nombre;
            $reg->cargo_firma    = $infoJefeFirma->cargo;
            $reg->fecha_real = Carbon::now('America/El_Salvador')->format('Y-m-d H:i:s');
            $reg->save();

            // ── Guardar detalle ────────────────────────────────────────
            $filaContada = 0;

            foreach ($datosContenedor as $filaArray) {
                $filaContada++;

                $infoFilaEntradaDetalle = EntradasDetalle::find($filaArray['infoIdEntradaDeta']);

                if (!$infoFilaEntradaDetalle) {
                    DB::rollback();
                    return ['success' => 2, 'fila' => $filaContada];
                }

                // Calcular disponible real desde salidas_detalle dinámicamente
                $totalSalido = DB::table('salidas_detalle')
                    ->where('id_entrada_detalle', $infoFilaEntradaDetalle->id)
                    ->sum('cantidad_salida');

                $disponibleReal = $infoFilaEntradaDetalle->cantidad_inicial - $totalSalido;

                // ── VALIDACIÓN 1: Cantidad supera disponible ───────────────
                if ($filaArray['infoCantidad'] > $disponibleReal) {
                    DB::rollback();
                    return [
                        'success'       => 2,
                        'fila'          => $filaContada,
                        'disponible'    => $disponibleReal,
                        'solicitado'    => $filaArray['infoCantidad'],
                    ];
                }

                // ── VALIDACIÓN 2: Fecha de salida anterior a fecha de entrada ──
                $fechaEntrada = $infoFilaEntradaDetalle->entrada->fecha ?? null;

                if ($fechaEntrada && $request->fecha < $fechaEntrada) {
                    DB::rollback();
                    return [
                        'success'      => 3,
                        'fila'         => $filaContada,
                        'fechaEntrada' => Carbon::parse($fechaEntrada)->format('d-m-Y'),
                        'fechaSalida'  => Carbon::parse($request->fecha)->format('d-m-Y'),
                    ];
                }

                // Guardar fila de detalle
                $detalle                     = new SalidasDetalle();
                $detalle->id_salida          = $reg->id;
                $detalle->id_entrada_detalle = $infoFilaEntradaDetalle->id;
                $detalle->cantidad_salida    = $filaArray['infoCantidad'];
                $detalle->tipo_regresa       = 0;
                $detalle->reemplazo          = $filaArray['infoReemplazo'];
                $detalle->recomendacion      = $filaArray['infoRecomendacion'];
                $detalle->mes_reemplazo      = $filaArray['infoMesReemplazo'];
                $detalle->completado         = 0;
                $detalle->save();
            }

            DB::commit();
            return ['success' => 10, 'idsalida' => $reg->id];

        } catch (\Throwable $e) {
            Log::error('guardarSalidaMateriales: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }


    // **** GENERAR PDF TEMPORAL ****


    public function generarPdfTemporal(Request $request)
    {
        // ── Datos básicos ──────────────────────────────────────────────
        $fecha         = $request->fecha;
        $idEmpleado    = $request->empleado;
        $lineaMaterial = $request->lineaMaterial;
        $idFirma       = $request->jefeFirma;

        $datos         = json_decode($request->contenedorArray, true);

        $infoQuienFirma = JefeFirma::where('id', $idFirma)->first();


        // ── Info del empleado con relaciones ───────────────────────────
        $infoEmpleado = Empleado::with(['unidadEmpleado', 'cargo', 'jefeDirecto'])
            ->findOrFail($idEmpleado);

        $infoUnidad  = $infoEmpleado->unidadEmpleado;
        $cargo       = $infoEmpleado->cargo->nombre ?? '—';
        $fechaFormat = date("d-m-Y", strtotime($fecha));

        // ── Jefe inmediato (nuevo schema) ──────────────────────────────
        // ── Jefe inmediato (desde jefe_unidad) ──────────────────────────
        if ($infoEmpleado->jefe == 1) {
            $jefeInmediato = $infoEmpleado->jefeDirecto?->nombre ?? '';
        } else {
            $jefeInmediato = DB::table('jefe_unidad')
                ->join('empleado', 'jefe_unidad.id_empleado', '=', 'empleado.id')
                ->where('jefe_unidad.id_unidad_empleado', $infoEmpleado->id_unidad_empleado)
                ->pluck('empleado.nombre')
                ->implode(' / ');

            $jefeInmediato = $jefeInmediato ?: '';
        }

        // ── Construir detalle en memoria ───────────────────────────────
        $detalle      = [];
        $totalColumna = 0;

        foreach ($datos as $fila) {
            $entradaDetalle = EntradasDetalle::find($fila['infoIdEntradaDeta']);
            $material       = Materiales::find($entradaDetalle->id_material);

            $cantidad     = intval($fila['infoCantidad']);
            $precio       = $entradaDetalle->precio;
            $multiplicado = $cantidad * $precio;
            $totalColumna += $multiplicado;

            $detalle[] = [
                'nombreMaterial' => $material->nombre,
                'cantidad'       => $cantidad,
                'precioFormat'   => '$' . number_format($precio, 2, '.', ','),
                'multiplicado'   => '$' . number_format($multiplicado, 2, '.', ','),
                'reemplazo'      => intval($fila['infoReemplazo'])    === 1 ? 'SI' : 'NO',
                'recomendacion'  => intval($fila['infoRecomendacion']) === 1 ? 'SI' : 'NO',
            ];
        }

        $totalColumnaValor = '$' . number_format($totalColumna, 2, '.', ',');
        $logoalcaldia      = 'images/logo.png';

        // ── Encabezado ─────────────────────────────────────────────────
        $tabla = "
    <table style='width:100%; border-collapse:collapse;'>
        <tr>
            <td style='width:15%; text-align:left;'>
                <img src='$logoalcaldia' alt='Logo' style='max-width:100px; height:auto;'>
            </td>
            <td style='width:70%; text-align:center;'>
                <h1 style='font-size:16px; margin:0; color:#003366; text-transform:uppercase;'>
                    ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                <h1 style='font-size:16px; margin:4px 0 0; color:#003366; text-transform:uppercase;'>
                    UNIDAD DE SEGURIDAD Y SALUD OCUPACIONAL.</h1>
            </td>
            <td style='width:15%;'></td>
        </tr>
    </table>
    <hr style='border:none; border-top:2px solid #003366; margin:4px 0 0;'>

    <div style='text-align:center; margin-top:20px;'>
        <h1 style='font-size:14px; margin:0; color:#000;'>
            Ficha de entrega de Equipo de Protección Personal (E.P.P.)</h1>
    </div>
    ";

        // Número de equipo
        if (!empty(trim($lineaMaterial))) {
            $tabla .= "
        <div style='text-align:right; margin-top:6px;'>
            <p style='font-size:13px; margin:0; color:#000;'>
                <strong>Número de equipo:</strong> " . trim($lineaMaterial) . "
            </p>
        </div>";
        }

        // ── Datos del empleado ─────────────────────────────────────────
        $tabla .= "
    <div style='text-align:left; margin-top:12px;'>
        <p style='font-size:12px; margin:0;'>Fecha: <strong>$fechaFormat</strong></p>
    </div>
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:12px; margin:0;'>
            Área: <strong>{$infoUnidad->nombre}</strong>&nbsp;&nbsp; Cargo: <strong>$cargo</strong>
        </p>
    </div>
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:12px; margin:0;'>
            Se entrega al colaborador(a): <strong>{$infoEmpleado->nombre}</strong>
        </p>
    </div>
    ";


        if (!empty(trim($jefeInmediato))) {
            $tabla .= "
            <div style='text-align:left; margin-top:8px;'>
                <p style='font-size:12px; margin:0;'>
                    Jefe inmediato: <strong>$jefeInmediato</strong>
                </p>
            </div>
            ";
        }

        $tabla .= "
        <div style='text-align:left; margin-top:8px;'>
            <p style='font-size:12px; margin:0;'>
                Por medio de la presente hace constar el detalle siguiente:
            </p>
        </div>
        ";

        // ── Tabla detalle ──────────────────────────────────────────────
        $tabla .= "
    <table width='100%' style='margin-top:14px; border-collapse:collapse;'>
        <thead>
            <tr>
                <th style='text-align:center; font-size:10px; width:10%; font-weight:bold; border:1px solid #000;' rowspan='2'>CANTIDAD</th>
                <th style='text-align:center; font-size:10px; width:25%; font-weight:bold; border:1px solid #000;' rowspan='2'>DESCRIPCION DE E.P.P.</th>
                <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' colspan='2'>REEMPLAZO</th>
                <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' rowspan='2'>VALOR</th>
                <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' rowspan='2'>VALOR TOTAL</th>
                <th style='text-align:center; font-size:10px; width:18%; font-weight:bold; border:1px solid #000;' rowspan='2'>
                    RECOMENDACIONES SOBRE EL USO Y MANTENIMIENTO DEL E.P.P. OTORGADO
                </th>
            </tr>
            <tr>
                <th style='text-align:center; font-size:10px; font-weight:bold; border:1px solid #000; width:5.5%;'>SI</th>
                <th style='text-align:center; font-size:10px; font-weight:bold; border:1px solid #000; width:5.5%;'>NO</th>
            </tr>
        </thead>
        <tbody>
    ";

        foreach ($detalle as $fila) {
            $si = $fila['reemplazo'] === 'SI' ? 'X' : '';
            $no = $fila['reemplazo'] === 'SI' ? '' : 'X';

            $tabla .= "
        <tr>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila['cantidad']}</td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'>{$fila['nombreMaterial']}</td>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>$si</td>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>$no</td>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila['precioFormat']}</td>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila['multiplicado']}</td>
            <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila['recomendacion']}</td>
        </tr>
        ";
        }

        // Fila total
        $tabla .= "
            <tr>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
                <td style='font-size:13px; border:1px solid #000; font-weight:bold; text-align:center; padding:5px;'>$totalColumnaValor</td>
                <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            </tr>
        </tbody>
    </table>
    ";

        // ── Texto compromiso ───────────────────────────────────────────
        $texto1 = "Esperando que dicho Equipo de protección personal cumpla con lo requerido, tendiendo un total de inversión de; "
            . "<strong>$totalColumnaValor</strong>"
            . " sea utilizado de la mejor manera. Yo me comprometo a utilizar el E.P.P. dentro de las horas laborales que me correspondes, correré con el total de la inversión para su reposición echa a mi persona cuando se me compruebe la venta de este equipo, el mal uso, la perdida, el deterioro por negligencia. El cual firmo la presente para constancia de recibido.";

        $tabla .= "
    <div style='text-align:justify; margin-top:30px; font-family:tahoma, arial, sans-serif;'>
        <p style='font-size:13px; margin:0; color:#000; line-height:1.6;'>$texto1</p>
    </div>
    ";

        // ── Firmas ─────────────────────────────────────────────────────
        $tabla .= "
    <table width='100%' style='margin-top:35px; border-collapse:collapse; font-family:tahoma, arial, sans-serif; font-size:13px;'>
        <tr>
            <td style='width:40%; text-align:center; vertical-align:top; padding:10px;'>
                <p style='margin:0; font-weight:bold; font-size:14px;'>Firma de Entregado.</p>
                <p style='margin:4px 0 0;'>$infoQuienFirma->nombre</p>
                <p style='margin:2px 0 0;'>$infoQuienFirma->cargo</p>
            </td>
            <td style='width:60%; text-align:left; vertical-align:top; padding:10px; padding-left:120px; padding-right:40px;'>
                <p style='margin:0; font-weight:bold; font-size:14px;'>FIRMA DE RECIBIDO</p>
                <p style='margin:4px 0 0;'>
                    <span style='font-weight:bold;'>DUI #</span> {$infoEmpleado->dui}
                </p>
            </td>
        </tr>
    </table>
    ";

        // ── Texto legal ────────────────────────────────────────────────
        $texto2 = "<span style='text-decoration:underline;'>CAPITULO ll INFRACCIONES DE PARTE DE LOS TRABAJADORES Art. 85.</span> – serán objeto de sanción conforme a la legislación vigente, los trabajadores que violen las siguientes medidas de seguridad e higiene: 1) Incumplir las ordenes e instrucciones dadas para garantizar su propia seguridad y salud, las de sus compañeros de trabajo y de terceras personas que se encuentren en el entorno. <span style='background-color:yellow;'>2) No utilizar correctamente los medios y equipos de protección personal facilitados por el empleador, de acuerdo con las instrucciones y regulaciones recibidas por este.</span> 3) No haber información inmediatamente a su jefe inmediato de cualquier situación que a su juicio pueda implicar un riesgo grave e inminente para la seguridad y salud ocupacional, así como de los defectos que hubiere comprobado en los sistemas de protección. Los trabajadores que violen estas disposiciones serán objeto de sanción, de conformidad a los estipulado en el Reglamento Interno de Trabajo de la Empresa, y si la contravención es manifestada y reiterada podrá el empleador dar por terminado su contrato de trabajo, de conformidad al artículo 50 número 17 del código de trabajo.";

        $tabla .= "
    <div style='text-align:justify; margin-top:30px; font-family:tahoma, arial, sans-serif;'>
        <p style='font-size:12px; margin:0; color:#000; line-height:1.6;'>$texto2</p>
    </div>
    ";

        // ── Generar PDF ────────────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Reporte Temporal');
        $mpdf->showImageErrors = false;

        $stylesheet = file_get_contents('css/cssbodega.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->setFooter("Página: {PAGENO}/{nb}");
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }



    public function generarPdfSalida($idsalida)
    {
        $infoSalida   = Salidas::findOrFail($idsalida);
        $infoEmpleado = Empleado::findOrFail($infoSalida->id_empleado);

        // ── Leer snapshot guardado en la salida ───────────────────────────
        // Si la columna tiene valor úsalo; si está vacía recalcula como fallback
        $colaborador   = !empty(trim($infoSalida->colaborador   ?? ''))
            ? $infoSalida->colaborador
            : $infoEmpleado->nombre;

        $area          = !empty(trim($infoSalida->area          ?? ''))
            ? $infoSalida->area
            : (UnidadEmpleado::find($infoEmpleado->id_unidad_empleado)?->nombre ?? '');

        $cargo         = !empty(trim($infoSalida->cargo         ?? ''))
            ? $infoSalida->cargo
            : (Cargo::find($infoEmpleado->id_cargo)?->nombre ?? '');


        // ── Jefe inmediato — snapshot o recalcular desde jefe_unidad ─────
        if (!empty(trim($infoSalida->jefe_inmediato ?? ''))) {
            $jefeInmediato = $infoSalida->jefe_inmediato;
        } else {
            $jefeInmediato = DB::table('jefe_unidad')
                ->join('empleado', 'jefe_unidad.id_empleado', '=', 'empleado.id')
                ->where('jefe_unidad.id_unidad_empleado', $infoEmpleado->id_unidad_empleado)
                ->pluck('empleado.nombre')
                ->implode(' / ') ?: '';
        }

        $fechaFormat = date("d-m-Y", strtotime($infoSalida->fecha));

        // ── Detalle de salida ──────────────────────────────────────────
        $arraySalidasDetalle = SalidasDetalle::where('id_salida', $idsalida)->get();
        $totalColumna        = 0;

        foreach ($arraySalidasDetalle as $item) {
            $infoEntradaDetalle = EntradasDetalle::find($item->id_entrada_detalle);
            $infoMaterial       = Materiales::find($infoEntradaDetalle->id_material);

            $item->precioFormat   = '$' . number_format($infoEntradaDetalle->precio, 2, '.', ',');
            $multiplicado         = $item->cantidad_salida * $infoEntradaDetalle->precio;
            $totalColumna        += $multiplicado;
            $item->multiplicado   = '$' . number_format($multiplicado, 2, '.', ',');
            $item->nombreMaterial = $infoMaterial->nombre;

            $esSi    = is_numeric($item->reemplazo)
                ? (intval($item->reemplazo) === 1)
                : (strtoupper(trim($item->reemplazo)) === 'SI');
            $esSiRec = is_numeric($item->recomendacion)
                ? (intval($item->recomendacion) === 1)
                : (strtoupper(trim($item->recomendacion)) === 'SI');

            $item->reemplazo     = $esSi    ? 'SI' : 'NO';
            $item->recomendacion = $esSiRec ? 'SI' : 'NO';
        }

        $totalColumnaValor = '$' . number_format($totalColumna, 2, '.', ',');
        $logoalcaldia      = 'images/logo.png';

        // ── Número de equipo ───────────────────────────────────────────
        $numeroEquipo = '';
        if (!empty(trim($infoSalida->material_linea ?? ''))) {
            $numeroEquipo = trim($infoSalida->material_linea);
        }

        // ══════════════════════════════════════════════════════════════
        // HTML DEL PDF
        // ══════════════════════════════════════════════════════════════

        // ── Encabezado con tabla institucional ────────────────────────
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
        <td style='width:50%; border-top:0.8px solid #000; border-bottom:0.8px solid #000; padding:6px 8px; text-align:center; font-size:15px; font-weight:bold;'>
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

        // ── Número de equipo (opcional) ────────────────────────────────
        if (!empty($numeroEquipo)) {
            $tabla .= "
    <div style='text-align:right; margin-top:6px;'>
        <p style='font-size:13px; margin:0; color:#000;'>
            <strong>Número de equipo:</strong> {$numeroEquipo}
        </p>
    </div>";
        }

        // ── Datos del empleado ─────────────────────────────────────────
        $tabla .= "
<div style='text-align:center; margin-top:5px;'>
    <h1 style='font-size:14px; margin:0; color:#000;'>
        Ficha de entrega de Equipo de Protección Personal (E.P.P.)
    </h1>
</div>

<div style='text-align:left; margin-top:12px;'>
    <p style='font-size:12px; margin:0;'>Fecha: <strong>{$fechaFormat}</strong></p>
</div>
<div style='text-align:left; margin-top:8px;'>
    <p style='font-size:12px; margin:0;'>
        Área: <strong>{$area}</strong>&nbsp;&nbsp; Cargo: <strong>{$cargo}</strong>
    </p>
</div>
<div style='text-align:left; margin-top:8px;'>
    <p style='font-size:12px; margin:0;'>
        Se entrega al colaborador(a): <strong>{$colaborador}</strong>
    </p>
</div>
";

        // ── Solo mostrar jefe si existe ────────────────────────────────
        if (!empty(trim($jefeInmediato))) {
            $tabla .= "
    <div style='text-align:left; margin-top:8px;'>
        <p style='font-size:12px; margin:0;'>
            Jefe inmediato: <strong>{$jefeInmediato}</strong>
        </p>
    </div>
    ";
        }

        $tabla .= "
<div style='text-align:left; margin-top:8px;'>
    <p style='font-size:12px; margin:0;'>
        Por medio de la presente hace constar el detalle siguiente:
    </p>
</div>
";

        // ── Tabla detalle ──────────────────────────────────────────────
        $tabla .= "
<table width='100%' style='margin-top:14px; border-collapse:collapse;'>
    <thead>
        <tr>
            <th style='text-align:center; font-size:10px; width:10%; font-weight:bold; border:1px solid #000;' rowspan='2'>CANTIDAD</th>
            <th style='text-align:center; font-size:10px; width:25%; font-weight:bold; border:1px solid #000;' rowspan='2'>DESCRIPCION DE E.P.P.</th>
            <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' colspan='2'>REEMPLAZO</th>
            <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' rowspan='2'>VALOR</th>
            <th style='text-align:center; font-size:10px; width:11%; font-weight:bold; border:1px solid #000;' rowspan='2'>VALOR TOTAL</th>
            <th style='text-align:center; font-size:10px; width:18%; font-weight:bold; border:1px solid #000;' rowspan='2'>
                RECOMENDACIONES SOBRE EL USO Y MANTENIMIENTO DEL E.P.P. OTORGADO
            </th>
        </tr>
        <tr>
            <th style='text-align:center; font-size:10px; font-weight:bold; border:1px solid #000; width:5.5%;'>SI</th>
            <th style='text-align:center; font-size:10px; font-weight:bold; border:1px solid #000; width:5.5%;'>NO</th>
        </tr>
    </thead>
    <tbody>
";

        foreach ($arraySalidasDetalle as $fila) {
            $si = $fila->reemplazo === 'SI' ? 'X' : '';
            $no = $fila->reemplazo === 'SI' ? '' : 'X';

            $tabla .= "
    <tr>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila->cantidad_salida}</td>
        <td style='font-size:12px; border:1px solid #000; padding:5px;'>{$fila->nombreMaterial}</td>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$si}</td>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$no}</td>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila->precioFormat}</td>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila->multiplicado}</td>
        <td style='font-size:12px; border:1px solid #000; text-align:center; padding:5px;'>{$fila->recomendacion}</td>
    </tr>
    ";
        }

        // ── Fila total ─────────────────────────────────────────────────
        $tabla .= "
        <tr>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
            <td style='font-size:13px; border:1px solid #000; font-weight:bold; text-align:center; padding:5px;'>{$totalColumnaValor}</td>
            <td style='font-size:12px; border:1px solid #000; padding:5px;'></td>
        </tr>
    </tbody>
</table>
";

        // ── Texto compromiso ───────────────────────────────────────────
        $texto1 = "Esperando que dicho Equipo de protección personal cumpla con lo requerido, tendiendo un total de inversión de; "
            . "<strong>{$totalColumnaValor}</strong>"
            . " sea utilizado de la mejor manera. Yo me comprometo a utilizar el E.P.P. dentro de las horas laborales que me correspondes, correré con el total de la inversión para su reposición echa a mi persona cuando se me compruebe la venta de este equipo, el mal uso, la perdida, el deterioro por negligencia. El cual firmo la presente para constancia de recibido.";

        $tabla .= "
<div style='text-align:justify; margin-top:30px; font-family:tahoma, arial, sans-serif;'>
    <p style='font-size:13px; margin:0; color:#000; line-height:1.6;'>{$texto1}</p>
</div>
";

        // ── Firmas ─────────────────────────────────────────────────────
        $tabla .= "
<table width='100%' style='margin-top:75px; border-collapse:collapse; font-family:tahoma, arial, sans-serif; font-size:13px;'>
    <tr>
        <td style='width:40%; text-align:center; vertical-align:top; padding:10px;'>
            <p style='margin:0; font-weight:bold; font-size:14px;'>Firma de Entregado.</p>
            <p style='margin:4px 0 0;'>$infoSalida->jefe_firma</p>
            <p style='margin:2px 0 0;'>$infoSalida->cargo_firma</p>
        </td>
        <td style='width:60%; text-align:left; vertical-align:top; padding:10px; padding-left:120px; padding-right:40px;'>
            <p style='margin:0; font-weight:bold; font-size:14px;'>FIRMA DE RECIBIDO</p>
            <p style='margin:4px 0 0;'>
                <span style='font-weight:bold;'>DUI #</span> {$infoEmpleado->dui}
            </p>
        </td>
    </tr>
</table>
";

        // ── Texto legal ────────────────────────────────────────────────
        $texto2 = "<span style='text-decoration:underline;'>CAPITULO ll INFRACCIONES DE PARTE DE LOS TRABAJADORES Art. 85.</span> – serán objeto de sanción conforme a la legislación vigente, los trabajadores que violen las siguientes medidas de seguridad e higiene: 1) Incumplir las ordenes e instrucciones dadas para garantizar su propia seguridad y salud, las de sus compañeros de trabajo y de terceras personas que se encuentren en el entorno. <span style='background-color:yellow;'>2) No utilizar correctamente los medios y equipos de protección personal facilitados por el empleador, de acuerdo con las instrucciones y regulaciones recibidas por este.</span> 3) No haber información inmediatamente a su jefe inmediato de cualquier situación que a su juicio pueda implicar un riesgo grave e inminente para la seguridad y salud ocupacional, así como de los defectos que hubiere comprobado en los sistemas de protección. Los trabajadores que violen estas disposiciones serán objeto de sanción, de conformidad a los estipulado en el Reglamento Interno de Trabajo de la Empresa, y si la contravención es manifestada y reiterada podrá el empleador dar por terminado su contrato de trabajo, de conformidad al artículo 50 número 17 del código de trabajo.";

        $tabla .= "
<div style='text-align:justify; margin-top:30px; font-family:tahoma, arial, sans-serif;'>
    <p style='font-size:12px; margin:0; color:#000; line-height:1.6;'>{$texto2}</p>
</div>
";

        // ── Generar PDF ────────────────────────────────────────────────
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf->SetTitle('Reporte Salida');
        $mpdf->showImageErrors = false;

        $stylesheet = file_get_contents('css/cssbodega.css');
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->setFooter("Página: {PAGENO}/{nb}");
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }




    public function buscarUnidadConDistrito(Request $request)
    {
        $regla = array(
            'id' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()) {
            return ['success' => 0];
        }

        $arrayUnidad = UnidadEmpleado::where('id_distrito', $request->id)->get();

        return ['success' => 1, 'arrayUnidad' => $arrayUnidad];
    }


    public function buscarUnidadConDistritoEmpleado(Request $request)
    {
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) return ['success' => 0];

        // ✅ AHORA — jefe desde tabla jefe_unidad (pivote)
        $jefeDeUnidad = DB::table('jefe_unidad')
            ->join('empleado', 'jefe_unidad.id_empleado', '=', 'empleado.id')
            ->where('jefe_unidad.id_unidad_empleado', $request->id)
            ->pluck('empleado.nombre')
            ->implode(' / ');  // → "ENRIQUE JOSE LOPEZ RIVAS / MAURICIO GIOVANY ROSALES HERNANDEZ"

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


}
