<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * JEFE DE UNIDAD - EMPLEADO
     */
    public function up(): void
    {
        Schema::create('jefe_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empleado')->constrained('empleado');
            $table->foreignId('id_unidad_empleado')->constrained('unidad_empleado');
            $table->unique(['id_empleado', 'id_unidad_empleado']); // evita duplicados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jefe_unidad');
    }
};
