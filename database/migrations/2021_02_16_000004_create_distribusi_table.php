<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistribusiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distribusi', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('id_paket_zakat')->nullable()->index();
            $table->string('panzisnas')->nullable();
            $table->string('panziswil')->nullable();
            $table->string('panzisda')->nullable();
            $table->string('cabang')->nullable();
            $table->string('mitra_strategis')->nullable();
            $table->string('duta')->nullable();
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
        Schema::dropIfExists('distribusi');
    }
}
