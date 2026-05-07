<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialTransferido extends Model
{
    use HasFactory;
    protected $table = 'historial_transf';
    public $timestamps = false;
}
