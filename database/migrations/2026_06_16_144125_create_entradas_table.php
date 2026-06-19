<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ENTRADAS
     */
    public function up(): void
    {
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_proveedor')->unsigned();
            $table->bigInteger('id_bodega')->unsigned();
            $table->date('fecha');
            $table->string('descripcion', 800)->nullable();

            $table->string('lote', 100)->nullable();

            $table->foreign('id_proveedor')->references('id')->on('proveedor');
            $table->foreign('id_bodega')->references('id')->on('bodega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entradas');
    }
};
