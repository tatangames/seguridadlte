<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoHerramientaDescartada extends Model
{
    use HasFactory;
    protected $table = 'histo_herramienta_descartada';
    public $timestamps = false;
}
