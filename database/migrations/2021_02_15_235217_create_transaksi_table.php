<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('no_kuitansi', 20)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi', 100)->nullable();
            $table->foreignId('id_users')->nullable()->index();
            $table->foreignId('id_donatur')->nullable()->index();
            $table->foreignId('id_lembaga')->nullable()->index();
            $table->foreignId('id_jenis_transaksi')->nullable()->index();
            $table->string('rek_bank')->nullable();
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
        Schema::dropIfExists('transaksi');
    }
}
