<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SALIDAS DETALLE
     */
    public function up(): void
    {
        Schema::create('salidas_detalle', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_salida')->unsigned();
            $table->bigInteger('id_entrada_detalle')->unsigned();

            $table->integer('cantidad_salida');

            // SABER SI ESTE MATERIAL REGRESARA DE NUEVO A BODEGA,
            // PARA QUE SALGA EN PENDIENTES DE REGRESO
            $table->boolean('tipo_regresa');

            $table->boolean('reemplazo');
            $table->boolean('recomendacion');

            // FECHA DE CAMBIO PARA EL USUARIO
            $table->integer('mes_reemplazo')->nullable();

            // CUANDO SE COMPLETE EL CAMBIO DE ITEM
            $table->boolean('completado')->default(0);



            $table->foreign('id_salida')->references('id')->on('salidas');
            $table->foreign('id_entrada_detalle')->references('id')->on('entradas_detalle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas_detalle');
    }
};
