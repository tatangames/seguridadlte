<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialTransfDetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_transf_deta', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_material')->unsigned();
            $table->bigInteger('id_historial_transf')->unsigned();

            $table->decimal('cantidad', 10, 2);

            $table->foreign('id_material')->references('id')->on('materiales');
            $table->foreign('id_historial_transf')->references('id')->on('historial_transf');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_transf_deta');
    }
}
