<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTransaksiKurban extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_transaksi_kurban', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('id_transaksi')->nullable()->index();
            $table->integer('manajer_status')->nullable();
            $table->integer('panzisda_status')->nullable();
            $table->integer('panziswil_status')->nullable();
            $table->text('komentar')->nullable();
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
        Schema::dropIfExists('status_transaksi_kurban');
    }
}
