<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialEntradasDetaTable extends Migration
{
    /**
     * HISTORIAL DE ENTRADAS DETALLE
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_entradas_deta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_material')->unsigned();
            $table->bigInteger('id_historial')->unsigned();

            // la entradas puede ser decimales
            $table->decimal('cantidad', 10, 2);

            $table->foreign('id_material')->references('id')->on('materiales');
            $table->foreign('id_historial')->references('id')->on('historial_entradas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_entradas_deta');
    }
}
