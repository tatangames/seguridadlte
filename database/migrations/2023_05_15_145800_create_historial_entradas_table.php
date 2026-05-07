<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialEntradasTable extends Migration
{
    /**
     * HISTORIAL DE ENTRADAS
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_entradas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipoproyecto')->unsigned();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // esta entrada debe ser para un proyecto o inventario general
            $table->foreign('id_tipoproyecto')->references('id')->on('tipoproyecto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_entradas');
    }
}
