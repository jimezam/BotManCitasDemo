<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaCitas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            // cliente
            $table->unsignedInteger('cliente_id');
            // servicio
            $table->unsignedInteger('servicio_id');
            // fecha y hora
            $table->dateTime('fecha');


            // Definición de llaves foráneas //

            // cliente
            $table->foreign('cliente_id')
                ->references('id')->on('clientes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // servicio
            $table->foreign('servicio_id')
                ->references('id')->on('servicios')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('citas');
    }
}
