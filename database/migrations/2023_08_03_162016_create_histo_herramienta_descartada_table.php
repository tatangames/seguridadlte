<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaDescartadaTable extends Migration
{
    /**
     * HISTORIAL DE HERRAMIENTAS DESCARTADAS
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_descartada', function (Blueprint $table) {
            $table->id();

            // este campo se utiliza si la herramienta estaba afuera de bodega y fue
            // robada o ya no funciona
            $table->bigInteger('id_histo_herra_salida')->unsigned()->nullable();
            $table->bigInteger('id_herramienta')->unsigned();


            $table->date('fecha');
            $table->integer('cantidad');
            $table->string('descripcion', 800)->nullable();


            $table->foreign('id_histo_herra_salida')->references('id')->on('histo_herramienta_salida');
            $table->foreign('id_herramienta')->references('id')->on('herramientas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histo_herramienta_descartada');
    }
}
