<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialSalidasDetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_salidas_deta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_material')->unsigned();
            $table->bigInteger('id_historial_salidas')->unsigned();

            // la entradas puede ser decimales
            $table->decimal('cantidad', 10, 2);

            $table->foreign('id_material')->references('id')->on('materiales');
            $table->foreign('id_historial_salidas')->references('id')->on('historial_salidas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_salidas_deta');
    }
}
