<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Marca;
use App\Models\Materiales;
use App\Models\Normativa;
use App\Models\ObjetoEspecifico;
use App\Models\Talla;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class MaterialesController extends Controller
{

    public function vistaMateriales()
    {
        $arrayUnidades        = UnidadMedida::orderBy('nombre', 'ASC')->get();
        $arrayMarcas          = Marca::orderBy('nombre', 'ASC')->get();
        $arrayNormativa       = Normativa::orderBy('nombre', 'ASC')->get();
        $arrayColor           = Color::orderBy('nombre', 'ASC')->get();
        $arrayTalla           = Talla::orderBy('nombre', 'ASC')->get();
        $arrayObjetoEspecifico = DB::table('objeto_especifico')
            ->orderBy('codigo', 'ASC')
            ->get();
        $lista = $this->obtenerListaMateriales();

        return view('backend.admin.materiales.vistamateriales', compact(
            'arrayUnidades', 'arrayMarcas', 'arrayNormativa',
            'arrayColor', 'arrayTalla', 'lista', 'arrayObjetoEspecifico',
        ));
    }


    public function tablaMateriales()
    {
        $lista = $this->obtenerListaMateriales();

        return view('backend.admin.materiales.tablamateriales', compact('lista'));
    }

    private function obtenerListaMateriales()
    {
        return DB::table('materiales as m')
            ->leftJoin('unidadmedida as um', 'um.id', '=', 'm.id_medida')
            ->leftJoin('marca as ma', 'ma.id', '=', 'm.id_marca')
            ->leftJoin('normativa as no', 'no.id', '=', 'm.id_normativa')
            ->leftJoin('color as co', 'co.id', '=', 'm.id_color')
            ->leftJoin('talla as ta', 'ta.id', '=', 'm.id_talla')
            ->leftJoin('objeto_especifico as oe', 'oe.id', '=', 'm.id_objespecifico')
            ->select(
                'm.*',
                'um.nombre as unidadMedida',
                'ma.nombre as marca',
                'no.nombre as normativa',
                'co.nombre as color',
                'ta.nombre as talla',
                'oe.codigo as oe_codigo',
                'oe.nombre as oe_nombre',
                DB::raw('(SELECT COALESCE(SUM(cantidad_inicial),0)
              FROM entradas_detalle
              WHERE id_material = m.id) as total_ingresado'),
                DB::raw('(SELECT COALESCE(SUM(sd.cantidad_salida),0)
              FROM salidas_detalle sd
              INNER JOIN entradas_detalle ed ON ed.id = sd.id_entrada_detalle
              WHERE ed.id_material = m.id) as total_salido'),
                DB::raw('(
        (SELECT COALESCE(SUM(cantidad_inicial),0) FROM entradas_detalle WHERE id_material = m.id)
        -
        (SELECT COALESCE(SUM(sd.cantidad_salida),0)
         FROM salidas_detalle sd
         INNER JOIN entradas_detalle ed ON ed.id = sd.id_entrada_detalle
         WHERE ed.id_material = m.id)
    ) as cantidadGlobal')
            )
            ->get();
    }




    public function nuevoMaterial(Request $request)
    {
        $regla = [
            'nombre'          => 'required',
            'unidad'          => 'required',
            'objeto_especifico' => 'required',
        ];

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) { return ['success' => 0]; }

        $registro = new Materiales();
        $registro->id_medida        = $request->unidad;
        $registro->id_marca         = $request->marca;
        $registro->id_normativa     = $request->normativa;
        $registro->id_color         = $request->color;
        $registro->id_talla         = $request->talla;
        $registro->nombre           = $request->nombre;
        $registro->codigo           = $request->codigo;
        $registro->otros            = $request->otros;
        $registro->meses_cambio     = $request->fecha;
        $registro->id_objespecifico = $request->objeto_especifico;

        if ($registro->save()) {
            return ['success' => 1];
        } else {
            return ['success' => 2];
        }
    }

    public function informacionMaterial(Request $request)
    {
        $regla    = ['id' => 'required'];
        $validar  = Validator::make($request->all(), $regla);
        if ($validar->fails()) { return ['success' => 0]; }

        if ($lista = Materiales::where('id', $request->id)->first()) {
            $arrayUnidad          = UnidadMedida::orderBy('nombre', 'ASC')->get();
            $arrayMarca           = Marca::orderBy('nombre', 'ASC')->get();
            $arrayNormativa       = Normativa::orderBy('nombre', 'ASC')->get();
            $arrayColor           = Color::orderBy('nombre', 'ASC')->get();
            $arrayTalla           = Talla::orderBy('nombre', 'ASC')->get();
            $arrayObjetoEspecifico = DB::table('objeto_especifico')
                ->orderBy('codigo', 'ASC')
                ->get();

            return [
                'success'           => 1,
                'material'          => $lista,
                'unidad'            => $arrayUnidad,
                'marca'             => $arrayMarca,
                'normativa'         => $arrayNormativa,
                'color'             => $arrayColor,
                'talla'             => $arrayTalla,
                'objeto_especifico' => $arrayObjetoEspecifico,
            ];
        } else {
            return ['success' => 2];
        }
    }

    public function editarMaterial(Request $request)
    {
        $regla = [
            'nombre'            => 'required',
            'unidad'            => 'required',
            'objeto_especifico' => 'required',
        ];

        $validar = Validator::make($request->all(), $regla);
        if ($validar->fails()) { return ['success' => 0]; }

        Materiales::where('id', $request->id)->update([
            'id_medida'         => $request->unidad,
            'id_marca'          => $request->marca,
            'id_normativa'      => $request->normativa,
            'id_color'          => $request->color,
            'id_talla'          => $request->talla,
            'nombre'            => $request->nombre,
            'codigo'            => $request->codigo,
            'otros'             => $request->otros,
            'meses_cambio'      => $request->fecha,
            'id_objespecifico'  => $request->objeto_especifico,
        ]);

        return ['success' => 1];
    }






}
