<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HerramientaPendiente extends Model
{
    use HasFactory;
    protected $table = 'herramienta_pendiente';
    public $timestamps = false;
}
