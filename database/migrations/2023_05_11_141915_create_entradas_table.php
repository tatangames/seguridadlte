<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntradasTable extends Migration
{
    /**
     * ES QUE LO QUE TENEMOS ACTUALMENTE EN INVENTARIO
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_material')->unsigned();
            $table->bigInteger('id_tipoproyecto')->unsigned();

            // la entradas puede ser decimales
            $table->decimal('cantidad', 10, 2);

            $table->foreign('id_material')->references('id')->on('materiales');
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
        Schema::dropIfExists('entradas');
    }
}
