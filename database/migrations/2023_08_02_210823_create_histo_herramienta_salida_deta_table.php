<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaSalidaDetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_salida_deta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_herra_salida')->unsigned();
            $table->bigInteger('id_herramienta')->unsigned();

            $table->integer('cantidad');

            $table->foreign('id_herra_salida')->references('id')->on('histo_herramienta_salida');
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
        Schema::dropIfExists('histo_herramienta_salida_deta');
    }
}
