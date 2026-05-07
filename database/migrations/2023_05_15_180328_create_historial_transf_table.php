<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialTransfTable extends Migration
{
    /**
     * HISTORIAL DE PROYECTOS TRANSFERIDOS A INVENTARIO GENERAL
     *
     * @return void
     */

    public function up()
    {
        Schema::create('historial_transf', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_tipoproyecto')->unsigned();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            // acta de cierre
            $table->string('documento', 100)->nullable();

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
        Schema::dropIfExists('historial_transf');
    }
}
