<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recorridos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('bicicleta_id');
            $table->double('calorias')->nullable()->default(0.0);
            $table->time('tiempo');
            $table->double('velocidad_promedio')->nullable()->default(0.0);
            $table->double('velocidad_maxima')->nullable()->default(0.0);
            $table->double('distancia_recorrida')->nullable()->default(0.0);
            $table->double('temperatura');
            $table->foreign('usuario_id')->references('id')->on('usuarios');
            $table->foreign('bicicleta_id')->references('id')->on('bicicletas');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recorridos');
    }
};
