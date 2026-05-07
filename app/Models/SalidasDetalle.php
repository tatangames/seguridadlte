<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidasDetalle extends Model
{
    use HasFactory;
    protected $table = 'salidas_detalle';
    public $timestamps = false;
}
