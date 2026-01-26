<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonaturTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donatur', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_donatur')->nullable();
            $table->string('nama', 50);
            $table->string('alamat', 300)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('no_hp', 13)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('penghasilan')->nullable();
            $table->string('tanggungan')->nullable();
            $table->string('status_rumah')->nullable();
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
        Schema::dropIfExists('donatur');
    }
}
