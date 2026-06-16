<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SALIDAS
     */
    public function up(): void
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('id_empleado')->constrained('empleado');
            $table->string('descripcion', 800)->nullable();
            $table->string('area', 300)->nullable();
            $table->string('cargo', 300)->nullable();
            $table->string('colaborador', 300)->nullable();
            $table->string('jefe_inmediato', 300)->nullable();

            $table->string('material_linea', 400)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
