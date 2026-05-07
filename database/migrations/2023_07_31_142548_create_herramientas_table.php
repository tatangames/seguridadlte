<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHerramientasTable extends Migration
{
    /**
     * Registro de Herramientas
     *
     * @return void
     */
    public function up()
    {
        Schema::create('herramientas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_medida')->unsigned()->nullable();

            $table->string('nombre', 300);
            $table->string('codigo', 100)->nullable();

            // se suma al hacer un ingreso.
            $table->integer('cantidad');

            $table->foreign('id_medida')->references('id')->on('unidadmedida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('herramientas');
    }
}
