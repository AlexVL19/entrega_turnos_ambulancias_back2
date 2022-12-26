<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovilCambiosSuspensionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movil_cambios_suspension', function (Blueprint $table) {
            $table->id('id_cambio_suspension');
            $table->unsignedInteger('id_equipo')->length(10);
            $table->foreign('id_equipo')->references('ID_Equipo')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->date('fecha_ultimo_cambio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movil_cambios_suspension');
    }
}
