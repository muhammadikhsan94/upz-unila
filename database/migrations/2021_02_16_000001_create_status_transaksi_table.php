<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatusTransaksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_transaksi', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('id_transaksi')->nullable()->index();
            $table->integer('manajer_status')->nullable();
            $table->integer('panzisda_status')->nullable();
            $table->integer('lazis_status')->nullable();
            $table->text('komentar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_transaksi');
    }
}
