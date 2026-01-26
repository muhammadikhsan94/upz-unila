<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTransaksiKurban extends Model
{
    protected $table = "status_transaksi_kurban";
    protected $primaryKey = "id";
    protected $fillable = ['id_transaksi', 'manajer_status', 'panzisda_status', 'panziswil_status', 'komentar'];
    public $timestamps = true;

    public function transaksi()
    {
        return $this->belongsTo('App\Models\TransakasiKurban', 'id', 'id_transaksi');
    }
}
