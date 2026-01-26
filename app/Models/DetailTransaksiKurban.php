<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiKurban extends Model
{
    use HasFactory;

    protected $table = "detail_transaksi_kurban";
    protected $primaryKey = "id";
    protected $fillable = ['id_transaksi', 'jenis', 'jumlah', 'nama'];
    public $timestamps = true;

    public function transaksi()
    {
        return $this->belongsTo('App\Models\TransaksiKurban', 'id', 'id_transaksi');
    }
}
