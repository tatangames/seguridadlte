<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * EMPLEADOS
     */
    public function up(): void
    {
        Schema::create('empleado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('dui', 50)->nullable();
            $table->foreignId('id_unidad_empleado')->constrained('unidad_empleado');
            $table->foreignId('id_cargo')->constrained('cargo');
            $table->boolean('jefe')->default(false);
            $table->unsignedBigInteger('id_jefe')->nullable();

            $table->boolean('activo')->default(true);

            $table->foreign('id_jefe')->references('id')->on('empleado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }
};
