<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materiales extends Model
{
    use HasFactory;

    protected $table = 'materiales';
    public $timestamps = false;


    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }

    public function unidadMedida(){
        return $this->belongsTo(UnidadMedida::class, 'id_medida');
    }


    public function normativa()
    {
        return $this->belongsTo(Normativa::class, 'id_normativa');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'id_color');
    }

    public function talla()
    {
        return $this->belongsTo(Talla::class, 'id_talla');
    }




}
