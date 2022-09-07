<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogMonitoreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_monitoreos', function (Blueprint $table) {
            $table->id();
            $table->string('empresa');
            $table->string('ruta_consult');
            $table->string('ruta_list');
            $table->string('ruta_notify');
            $table->integer('company_id');
            $table->integer('num_row_searched');
            $table->integer('status');
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
        Schema::dropIfExists('log_monitoreos');
    }
}
