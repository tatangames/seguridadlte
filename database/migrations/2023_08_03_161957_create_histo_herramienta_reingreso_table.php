<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaReingresoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_reingreso', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_histo_herra_salida')->unsigned();
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
        Schema::dropIfExists('histo_herramienta_reingreso');
    }
}
