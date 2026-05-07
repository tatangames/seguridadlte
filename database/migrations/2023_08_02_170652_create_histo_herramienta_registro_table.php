<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaRegistroTable extends Migration
{
    /**
     * REGISTRAR NUEVA HERRAMIENTA A LA BODEGA
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_registro', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histo_herramienta_registro');
    }
}
