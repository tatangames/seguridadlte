<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UNIDAD DEL EMPLEADO
     */
    public function up(): void
    {
        Schema::create('unidad_empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_distrito')->constrained('distrito');
            $table->string('nombre', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_empleado');
    }
};
