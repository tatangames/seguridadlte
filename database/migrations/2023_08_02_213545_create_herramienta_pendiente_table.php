<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHerramientaPendienteTable extends Migration
{
    /**
     * ES LA HERRAMIENTA QUE ESTA AFUERA
     *
     * @return void
     */
    public function up()
    {
        Schema::create('herramienta_pendiente', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('id_histo_herra_salida')->unsigned();
            $table->bigInteger('id_herramienta')->unsigned();

            $table->date('fecha');
            $table->integer('cantidad');

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
        Schema::dropIfExists('herramienta_pendiente');
    }
}
