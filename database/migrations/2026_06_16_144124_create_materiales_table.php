<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_medida')->unsigned();
            $table->bigInteger('id_marca')->unsigned()->nullable();
            $table->bigInteger('id_normativa')->unsigned()->nullable();
            $table->bigInteger('id_color')->unsigned()->nullable();
            $table->bigInteger('id_talla')->unsigned()->nullable();
            $table->bigInteger('id_objespecifico')->unsigned()->nullable();

            $table->string('nombre', 300);
            $table->string('codigo', 100)->nullable();
            $table->string('otros', 500)->nullable();

            // ESTIMADO PARA CADA MATERIAL
            $table->integer('meses_cambio')->nullable();

            $table->foreign('id_medida')->references('id')->on('unidadmedida');
            $table->foreign('id_marca')->references('id')->on('marca');
            $table->foreign('id_normativa')->references('id')->on('normativa');
            $table->foreign('id_objespecifico')->references('id')->on('objeto_especifico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
