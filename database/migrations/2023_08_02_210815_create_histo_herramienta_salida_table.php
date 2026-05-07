<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoHerramientaSalidaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histo_herramienta_salida', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();


            $table->bigInteger('quien_recibe')->unsigned();
            $table->bigInteger('quien_entrega')->unsigned();

            // # de salida de herramienta
            $table->string('num_salida', 100)->nullable();


            $table->foreign('quien_recibe')->references('id')->on('quienrecibe');
            $table->foreign('quien_entrega')->references('id')->on('quienentrega');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histo_herramienta_salida');
    }
}
