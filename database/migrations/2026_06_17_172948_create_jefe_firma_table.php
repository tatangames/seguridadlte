<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NOMBRE JEFE QUE VA A FIRMAR
     */
    public function up(): void
    {
        Schema::create('jefe_firma', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('cargo', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jefe_firma');
    }
};
