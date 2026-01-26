<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKurban extends Model
{
    protected $table = "transaksi_kurban";
    protected $primaryKey = "id";
    protected $fillable = ['id_users', 'lokasi', 'tgl_transaksi'];
    public $timestamps = true;

    public function status()
    {
        return $this->hasOne('App\Models\StatusTransaksiKurban', 'id_transaksi', 'id');
    }

    public function detail_transaksi()
    {
        return $this->hasMany('App\Models\DetailTransaksiKurban', 'id_transaksi', 'id');
    }

    public function delete()
    {
        // delete all related contacts
        $this->detail_transaksi()->delete();
        $this->status()->delete();

        // delete the customer
        return parent::delete();
    }
}
