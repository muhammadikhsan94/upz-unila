<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\PaketZakat;
use App\Models\Lembaga;
use App\Models\Donatur;
use App\Models\StatusTransaksi;
use App\Models\Barang;
use App\Models\Jabatan;
use App\Models\Group;
use App\Models\Perencanaan;
use App\Models\Role;
use App\Models\DetailTransaksi;
use App\Models\Distribusi;
use App\Models\JenisTransaksi;
use App\Models\RekeningLembaga;
use App\Models\Wilayah;
use DataTables;
use Auth;
use Carbon\Carbon;
use Charts;
use DB;
use App\Mail\MailNotify;
use Mail;
use App\Mail\ResetPasswordNotify;
use Session;
use App\Models\TransaksiKurban;
use App\Models\DetailTransaksiKurban;
use App\Models\StatusTransaksiKurban;

class PanzisdaController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('panzisda');

		$this->user_panzisda  = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*', 'role.id_jabatan', 'role.id_atasan')
								->where('role.id_jabatan', 2)
								->get();

		$this->user_duta    = DB::table('users')
							->join('role','role.id_users','=','users.id')
							->join('group','group.id','=','role.id_group')
							->select('users.*', 'role.id_jabatan', 'role.id_atasan', 'role.id_group', 'group.target')
							->where('role.id_jabatan', 5)
							->get();

		$this->user         = DB::table('users')
							->join('role','role.id_users','=','users.id')
							->leftJoin('wilayah','wilayah.id','=','users.id_wilayah')
							->select('users.*','role.id_jabatan', 'wilayah.nama_wilayah')
							->where('role.id_jabatan', '!=', 5)
							->get();

		$this->user_manajer     = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*', 'role.id_jabatan', 'role.id_atasan')
								->where('role.id_jabatan', 4)
								->get();

		$this->user_manajerarea  = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*', 'role.id_jabatan', 'role.id_atasan')
								->where('role.id_jabatan', 3)
								->get();

		$this->user_lazis  = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*', 'role.id_jabatan', 'role.id_atasan')
								->where('role.id_jabatan', 6)
								->get();

		$this->user_panziswil       = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*', 'role.id_jabatan', 'role.id_atasan')
								->where('role.id_jabatan', 1)
								->get();
	}

	public function index()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();

		$manajer                        = DB::table('users')
									->where('users.deleted_at', NULL)
									->join('role','role.id_users','=','users.id')
									->join('wilayah','wilayah.id','=','users.id_wilayah')
									->select('users.id', 'users.nama', 'users.no_punggung')
									->where('users.id_wilayah', Auth::user()->id_wilayah)
									->where('role.id_jabatan', '4')
									->get();

		$tmp1 = [];
		foreach ($manajer as $item) {
			$targets         = DB::table('users')
							->where('users.deleted_at', NULL)
							->join('role','role.id_users','=','users.id')
							->where('role.id_atasan', $item->id)
							->where('role.id_jabatan', 5)
							->join('group','group.id','=','role.id_group')
							->select(DB::raw('CAST(SUM(group.target) as UNSIGNED) as target'))
							->first();

			$y              = DB::table('users')
							->where('users.deleted_at', NULL)
							->join('role','role.id_users','=','users.id')
							->where('role.id_atasan', $item->id)
							->where('role.id_jabatan', 5)
							->leftJoin('transaksi','transaksi.id_users','=','users.id')
							->leftJoin('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
							->leftJoin('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
							->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as y'))
							->where('status_transaksi.lazis_status', '!=', NULL)
							->first();

			$dummy['name']          = $item->no_punggung;
			$dummy['nama']          = ucwords($item->nama);
			$dummy['y']             = ($y->y != NULL) ? $y->y : 0;
			$dummy['y_']            = format_uang($y->y);
			$dummy['target']        = ($targets->target != NULL) ? $targets->target : 0;
			$dummy['targets']       = format_uang($targets->target);
			$dummy['persentase']    = ($y->y != 0 && $targets->target != 0) ? number_format(($y->y / $targets->target) * 100, 2) : 0;
			$dummy['drilldown']     = $item->no_punggung;
			$tmp1[]                 = $dummy;
		}
		$data['manajer']                = $tmp1;

		//DUTA
		$tmp2 = [];
		foreach ($manajer as $value) {
			$dummys['name'] = $value->nama;
			$dummys['id']   = $value->no_punggung;

			$duta           = DB::table('users')
							->join('role', 'role.id_users','=','users.id')
							->where('role.id_jabatan', 5)
							->where('role.id_atasan', $value->id)
							->select('users.*')
							->get();

			$tmp3 = [];
			foreach($duta as $value1) {
				$target     = DB::table('users')
							->where('users.id', $value1->id)
							->join('role', 'role.id_users','=','users.id')
							->join('group', 'group.id','=','role.id_group')
							->select('group.target as target')
							->first();

				$y          = DB::table('users')
							->where('users.id', $value1->id)
							->join('transaksi','transaksi.id_users','=','users.id')
							->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
							->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
							->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as y'))
							->where('status_transaksi.lazis_status', '!=', NULL)
							->first();

				$dummyss['name'] = $value1->no_punggung;
				$dummyss['nama'] = ucwords($value1->nama);
				$dummyss['y'] = ($y->y != NULL) ? $y->y : 0;
				$dummyss['y_'] = format_uang($dummyss['y']);
				$dummyss['target'] = format_uang($targets->target);
				$dummyss['persentase'] = ($dummyss['y'] != 0 && $targets->target != 0) ? number_format(($dummyss['y'] / $targets->target) * 100, 2) : 0;
				$tmp3[] = $dummyss;
			}

			$dummys['data'] = $tmp3;
			$tmp2[] = $dummys;
		}
		$data['duta'] = $tmp2;

		//Realisasi
		$target         = DB::table('users')
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('group','group.id','=','role.id_group')
						->select(DB::raw('CAST(SUM(group.target) as UNSIGNED) as target'))
						->get();

		$y              = DB::table('users')
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->leftJoin('transaksi','transaksi.id_users','=','users.id')
						->leftJoin('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->leftJoin('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
						->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as y'), 'status_transaksi.lazis_status')
						->groupBy('status_transaksi.lazis_status')
						->orderBy('status_transaksi.lazis_status', 'DESC')
						->get();

		foreach($target as $value1) {
			$value1->name = Wilayah::where('id', Auth::user()->id_wilayah)->pluck('nama_wilayah')->first();

			$sumY = 0;
			foreach($y as $value2) {
				if ($value2->lazis_status != null) {
					$sumY = $sumY + $value2->y;
				}
			}

			$value1->y = $sumY;
			$value1->y_ = format_uang($sumY);
			$value1->target_ = format_uang($value1->target);
			$value1->persentase = ($sumY != 0 && $value1->target != 0) ? number_format(($sumY / $value1->target) * 100, 2) : 0;
		}

		$data['realisasi']        = $target;

		//paket zakat
		$paketzakat                 = DB::table('transaksi')
									->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
									->join('paketzakat', 'detail_transaksi.id_paket_zakat','=','paketzakat.id')
									->join('status_transaksi','transaksi.id','=','status_transaksi.id_transaksi')
									->select('paketzakat.nama_paket_zakat as name', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as y'))
									->whereIn('transaksi.id_users', function($query){
										$query->select('id')->from('users')->where('id_wilayah', Auth::User()->id_wilayah)->get();
									})
									->where('status_transaksi.lazis_status', '!=', NULL)
									->groupBy('name')
									->get()->toArray();
		// dd($paketzakat);

		$countY = 0;
		foreach ($paketzakat as $item) {
			$countY = $countY + $item->y;
		}

		$tmp3 = [];
		foreach ($paketzakat as $item) {
			$dummyss['name'] = $item->name;
			$dummyss['y'] = $item->y;
			$dummyss['persentase'] = number_format(($item->y / $countY) * 100, 2);
			$tmp3[] = $dummyss;
		}
		$data['paketzakat'] = $tmp3;

		return view('admin.panzisda.beranda', compact('data'));
	}

	public function getTransaksi()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();

		return view('admin.panzisda.transaksi', compact('data'));
	}

	public function getUser()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.user', compact('data'));
	}

	public function tambahUser()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['jabatan']        = Jabatan::whereNotIn('id', [1,2,6])->get();
		$data['manajerarea']    = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*')
								->where('role.id_jabatan', 3)
								->where('users.id_wilayah', Auth::user()->id_wilayah)
								->get()->toArray();
		$data['manajer']        = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*')
								->where('role.id_jabatan', 4)
								->where('users.id_wilayah', Auth::user()->id_wilayah)
								->get()->toArray();
		$data['group']          = Group::all();
		return view('admin.panzisda.tambahuser', compact('data'));
	}

	public function simpanUser(Request $request)
	{
		//USER
		$nubrow = count(User::all())+1;

		$user = new User();
		$user->no_punggung = str_pad(Auth::user()->id_wilayah, 2, '0', STR_PAD_LEFT).str_pad($nubrow, 4, 0, STR_PAD_LEFT);
		$user->nama = $request->nama;
		$user->alamat = $request->alamat;
		$user->npwp = $request->npwp;
		$user->no_hp = $request->no_hp;
		$user->email = $request->email;
		$user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password
		$user->id_wilayah = Auth::user()->id_wilayah;
		$user->save();

		//JABATAN
		foreach ($request->id_jabatan as $id_jabatan) {
			if ($id_jabatan == "5") {
				$role = new Role();
				$role->id_users = $user->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->manajer_id;
				$role->id_group = $request->group_id;
				$role->save();
			} else if ($id_jabatan == "4") {
				$role = new Role();
				$role->id_users = $user->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->manajerarea_id;
				$role->save();
			} else if ($id_jabatan == "3") {
				$role = new Role();
				$role->id_users = $user->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->panzisda_id;
				$role->save();
			} else {
				$role = new Role();
				$role->id_users = $user->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_lembaga = $request->id_lembaga;
				$role->save();
			}
		}

		// Mail::to($user->email)->send(new MailNotify($user));

		return response()->json(['success' => 'success stored!']);
	}

	public function getDataTransaksi($id)
	{
		if ($id == 0) {
			$transaksi  = DB::table('transaksi')
						->whereIn('transaksi.id_users', function($request){
							$request->select('id')->from('users')
							->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
						})
						->join('lembaga', 'transaksi.id_lembaga','=','lembaga.id')
						->join('users', 'transaksi.id_users','=','users.id')
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
						->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
						->join('donatur','transaksi.id_donatur','=','donatur.id')
						->join('status_transaksi', 'transaksi.id','=','status_transaksi.id_transaksi')
						->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
						->select('status_transaksi.panzisda_status', 'transaksi.id', 'donatur.nama as donatur', 'users.nama as user', 'jenis_transaksi.jenis_transaksi', 'lembaga.nama_lembaga as lembaga', 'status_transaksi.manajer_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id as status_id', 'status_transaksi.updated_at as update', 'role.id_atasan as atasan', 'status_transaksi.lazis_status')
						->orderBy(DB::raw('status_transaksi.panzisda_status IS NULL'), 'DESC')
						->orderBy(DB::raw('status_transaksi.manajer_status IS NULL'), 'ASC')
						->orderBy('transaksi.id', 'DESC')
						->groupBy('transaksi.id','donatur.nama','users.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id', 'status_transaksi.updated_at', 'role.id_atasan', 'status_transaksi.lazis_status')
						->get();
		} else if ($id == 1) {
			$transaksi  = DB::table('transaksi')
						->whereIn('transaksi.id_users', function($request){
							$request->select('id')->from('users')
							->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
						})
						->join('lembaga', 'transaksi.id_lembaga','=','lembaga.id')
						->join('users', 'transaksi.id_users','=','users.id')
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
						->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
						->join('donatur','transaksi.id_donatur','=','donatur.id')
						->join('status_transaksi', 'transaksi.id','=','status_transaksi.id_transaksi')
						->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
						->select('status_transaksi.panzisda_status', 'transaksi.id', 'donatur.nama as donatur', 'users.nama as user', 'jenis_transaksi.jenis_transaksi', 'lembaga.nama_lembaga as lembaga', 'status_transaksi.manajer_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id as status_id', 'status_transaksi.updated_at as update', 'role.id_atasan as atasan', 'status_transaksi.lazis_status')
						->where('status_transaksi.panzisda_status', '!=', NULL)
						->orderBy('transaksi.id', 'DESC')
						->groupBy('transaksi.id','donatur.nama','users.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id', 'status_transaksi.updated_at','role.id_atasan', 'status_transaksi.lazis_status')
						->get();
		} else if ($id == 2) {
			$transaksi  = DB::table('transaksi')
						->whereIn('transaksi.id_users', function($request){
							$request->select('id')->from('users')
							->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
						})
						->join('lembaga', 'transaksi.id_lembaga','=','lembaga.id')
						->join('users', 'transaksi.id_users','=','users.id')
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
						->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
						->join('donatur','transaksi.id_donatur','=','donatur.id')
						->join('status_transaksi', 'transaksi.id','=','status_transaksi.id_transaksi')
						->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
						->select('status_transaksi.panzisda_status', 'transaksi.id', 'donatur.nama as donatur', 'users.nama as user', 'jenis_transaksi.jenis_transaksi', 'lembaga.nama_lembaga as lembaga', 'status_transaksi.manajer_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id as status_id', 'status_transaksi.updated_at as update', 'role.id_atasan as atasan', 'status_transaksi.lazis_status')
						->where('status_transaksi.komentar', '!=', NULL)
						->where('status_transaksi.updated_at', NULL)
						->orderBy('transaksi.id', 'DESC')
						->groupBy('transaksi.id','donatur.nama','users.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id', 'status_transaksi.updated_at', 'role.id_atasan', 'status_transaksi.lazis_status')
						->get();
		} else if ($id == 3) {
			$transaksi  = DB::table('transaksi')
						->whereIn('transaksi.id_users', function($request){
							$request->select('id')->from('users')
							->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
						})
						->join('lembaga', 'transaksi.id_lembaga','=','lembaga.id')
						->join('users', 'transaksi.id_users','=','users.id')
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
						->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
						->join('donatur','transaksi.id_donatur','=','donatur.id')
						->join('status_transaksi', 'transaksi.id','=','status_transaksi.id_transaksi')
						->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
						->select('status_transaksi.panzisda_status', 'transaksi.id', 'donatur.nama as donatur', 'users.nama as user', 'jenis_transaksi.jenis_transaksi', 'lembaga.nama_lembaga as lembaga', 'status_transaksi.manajer_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id as status_id', 'status_transaksi.updated_at as update', 'role.id_atasan as atasan', 'status_transaksi.lazis_status')
						->where('status_transaksi.manajer_status', '!=', NULL)
						->where('status_transaksi.panzisda_status', NULL)
						->orderBy('transaksi.id', 'DESC')
						->groupBy('transaksi.id','donatur.nama','users.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id', 'status_transaksi.updated_at', 'role.id_atasan', 'status_transaksi.lazis_status')
						->get();
		} else {
			$transaksi  = DB::table('transaksi')
						->whereIn('transaksi.id_users', function($request){
							$request->select('id')->from('users')
							->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
						})
						->join('lembaga', 'transaksi.id_lembaga','=','lembaga.id')
						->join('users', 'transaksi.id_users','=','users.id')
						->where('users.deleted_at', NULL)
						->join('role','role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
						->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
						->join('donatur','transaksi.id_donatur','=','donatur.id')
						->join('status_transaksi', 'transaksi.id','=','status_transaksi.id_transaksi')
						->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
						->select('status_transaksi.panzisda_status', 'transaksi.id', 'donatur.nama as donatur', 'users.nama as user', 'jenis_transaksi.jenis_transaksi', 'lembaga.nama_lembaga as lembaga', 'status_transaksi.manajer_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id as status_id', 'status_transaksi.updated_at as update', 'role.id_atasan as atasan', 'status_transaksi.lazis_status')
						->where('status_transaksi.manajer_status', NULL)
						->where('status_transaksi.komentar', NULL)
						->orWhere('status_transaksi.updated_at', '!=', NULL)
						->orderBy('transaksi.id', 'DESC')
						->groupBy('transaksi.id','donatur.nama','users.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'transaksi.tanggal_transfer', 'status_transaksi.komentar', 'status_transaksi.id', 'status_transaksi.updated_at', 'role.id_atasan', 'status_transaksi.lazis_status')
						->get();
		}

		foreach ($transaksi as $item) {
			$manajer = User::where('id', $item->atasan)->first();
			$item->atasan = $manajer->nama;
		}

		return DataTables::of($transaksi)
		->addIndexColumn()
		->addColumn('aksi', function($transaksi) {
			if ($transaksi->manajer_status == NULL) {
				$button = '<center><button type="button" name="detail" id="'.$transaksi->id.'" class="detail btn btn-secondary btn-xs" disabled>VERIFIKASI</button></center>';
			} else {
				$button = '<center><button type="button" name="detail" id="'.$transaksi->id.'" class="detail btn btn-secondary btn-xs">VERIFIKASI</button></center>';
			}
			return $button;
		})
		->editColumn('panzisda_status', function($transaksi){
			if (($transaksi->manajer_status == null and $transaksi->komentar == null) or $transaksi->update != null) {
                $text = '<b style="color:brown;">TUNGGU PROSES JURUSAN</b>';
            } else if ($transaksi->manajer_status != NULL and $transaksi->panzisda_status == null and $transaksi->lazis_status == null) {
                $text = '<b style="color:brown;">TUNGGU PROSES FAKULTAS</b>';
            } else if ($transaksi->manajer_status != NULL and $transaksi->panzisda_status != null and $transaksi->lazis_status == null) {
                $text = '<b style="color:brown;">TUNGGU PROSES UPZ</b>';
            } else if ($transaksi->manajer_status == null and $transaksi->komentar != null) {
                $text = '<b style="color:red;">TIDAK VALID</b>';
            } else if ($transaksi->lazis_status != NULL) {
                $text = '<b style="color:green;">VALID</b>';
            }
			return $text;
		})
		->editColumn('lembaga', function($transaksi){
			return strtoupper($transaksi->lembaga);
		})
		->editColumn('jumlah', function($transaksi){
			return format_uang_with_rp($transaksi->jumlah);
		})
		->editColumn('jenis_transaksi', function($transaksi){
			return strtoupper($transaksi->jenis_transaksi);
		})
		->editColumn('tanggal_transfer', function($transaksi){
			return date('d/m/Y', strtotime($transaksi->tanggal_transfer));
		})
		->rawColumns(['panzisda_status', 'aksi'])
		->make(true);
	}

	public function getDataUser()
	{
		$user       = DB::table('users')
					->where('users.deleted_at', NULL)
					->leftJoin('role','role.id_users','=','users.id')
					->leftJoin('jabatan','jabatan.id','=','role.id_jabatan')
					->whereNotIn('jabatan.id', [1,2,6])
					->select('users.id', 'users.nama', 'users.no_hp', 'users.no_punggung', DB::raw('group_concat(jabatan.nama_jabatan SEPARATOR ", ") as jabatan'), DB::raw('group_concat(IF(role.id_atasan IS NULL, "null", role.id_atasan)) as id_atasan'), DB::raw('group_concat(IF(role.id_group IS NULL, "null", role.id_group)) as id_group'))
					->where('users.id_wilayah', Auth::user()->id_wilayah)
					->where('users.no_punggung', '!=', '000001')
					->groupBy('users.id','users.no_punggung','users.nama', 'users.no_hp')
					->orderBy('users.no_punggung', 'ASC')
					->get();

		return DataTables::of($user)
			->addIndexColumn()
			->addColumn('aksi', function($user) {

				$jabatan = explode(",", $user->jabatan);
				$atasan = explode(",", $user->id_atasan);
				$group = explode(",", $user->id_group);
				$status = 0;
				$id_group = 0;

				for ($i=0;$i<count($jabatan);$i++) {
					if (($jabatan[$i] == "null") OR ($jabatan[$i] == "5" AND $atasan[$i] == "null") OR ($jabatan[$i] == "5" AND $group[$i] == "null") OR ($jabatan[$i] == "3" AND $atasan[$i] == "null") OR ($jabatan[$i] == "4" AND $atasan[$i] == "null")) {
						$status = 1;
						break;
					}
				}

				if ($status == 1) {
					$button = '<center><button type="button" name="edit" id="'.$user->id.'" class="edit btn btn-warning btn-xs">Verifikasi</button>&nbsp;<button type="button" name="delete" id="'.$user->id.'" class="delete btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></button></center>';
				} else {
					$button = '<center><button type="button" name="edit" id="'.$user->id.'" class="edit btn btn-secondary btn-xs"><i class="fa fa-edit"></i></button>&nbsp;<button type="button" name="delete" id="'.$user->id.'" class="delete btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></button></center>';
				}
				return $button;

			})
			->editColumn('id_group', function($user){
				$group = explode(",", $user->id_group);
				for ($i=0;$i<count($group);$i++) {
					if ($group[$i] != "null") {
						return $group[$i];
					}
				}
			})
			->rawColumns(['aksi'])
			->make(true);

	}

	public function editUser($id)
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['editUser']       = User::find($id);
		$data['role']           = Role::where('id_users', $id)->get();
		$data['group']          = Group::all();
		$data['panzisda']       = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*')
								->where('users.id_wilayah', $data['editUser']->id_wilayah)
								->where('role.id_jabatan', 2)
								->get()->toArray();
		$data['manajerarea']    = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*')
								->where('role.id_jabatan', 3)
								->where('users.id_wilayah', $data['editUser']->id_wilayah)
								->get()->toArray();
		$data['manajer']        = DB::table('users')
								->join('role','role.id_users','=','users.id')
								->select('users.*')
								->where('role.id_jabatan', 4)
								->where('users.id_wilayah', $data['editUser']->id_wilayah)
								->get()->toArray();
		$data['jabatan']        = Jabatan::whereNotIn('id', [1, 2, 6])->get();
		$data['lembaga']        = Lembaga::all();

		$tmp = [];
		foreach ($data['role'] as $item) {
			$tmp[] = $item->id_jabatan;
		}
		$data['tmp'] = $tmp;

		return view('admin.panzisda.edituser', compact('data'));
	}

	public function updateUser(Request $request)
	{
		$user = User::find($request->id);

		if (empty($user)) {
			return response()->json(['errors' => [0 => 'Data not found !']]);
		}

		$user->no_punggung = $request->no_punggung;
		$user->nama = $request->nama;
		$user->alamat = $request->alamat;
		$user->npwp = $request->npwp;
		$user->no_hp = $request->no_hp;
		$user->email = $request->email;
		$user->id_wilayah = $request->id_wilayah;
		$user->save();

		$role_user = Role::where('id_users', $request->id)->get();
		foreach ($role_user as $item) {
			$item->delete();
		}

		foreach ($request->jabatan_id as $id_jabatan) {

			$role = new Role();

			if ($id_jabatan == 6) {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_lembaga = $request->edit_lembaga;
				$role->save();
			} else if ($id_jabatan == 5) {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->manajer_id;
				$role->id_group = $request->group_id;
				$role->save();
			} else if ($id_jabatan == 4) {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->spv_id;
				$role->save();
			} else if ($id_jabatan == 3) {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = $request->panzisda_id;
				$role->save();
			} else if ($id_jabatan == 2) {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->id_atasan = Auth::user()->id;
				$role->save();
			} else {
				$role->id_users = $request->id;
				$role->id_jabatan = $id_jabatan;
				$role->save();
			}
		};

		return response()->json(['success' => 'update success stored!']);
	}

	public function getStatus($id)
	{
		$data = Transaksi::find($id);

		return json_encode($data);
	}

	public function updateStatus(Request $request)
	{
		if ($request->setujui == 'OK') {
			$status = StatusTransaksi::where('id_transaksi', $request->idTrx1)->first();
			$status->panzisda_status = Auth::user()->id;
			$status->updated_at = null;
			$status->komentar = null;

			$dana_mandiri = Transaksi::where('id', $request->idTrx1)
							->where('id_lembaga', function($query) {
								$query->select('id')->from('lembaga')->whereIn('nama_lembaga', ['dana mandiri', 'Dana Mandiri', 'DANA MANDIRI'])->pluck('id');
							})->first();

			if ($dana_mandiri != NULL) {
				$status->lazis_status = Auth::user()->id;
			}
			$status->save();
		} else {
			$status = StatusTransaksi::where('id_transaksi', $request->idTrx2)->first();
			$status->manajer_status = null;
			$status->panzisda_status = null;
			$status->updated_at = null;
			$status->komentar = $request->komentar;
			$status->save();
		}
	}

	public function resetPassword(Request $request)
	{
		$user = User::find($request->id);
		$user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

		if(!$user->save()) {
			return redirect('/fakultas_lembaga/user')->with(['errors' => 'Gagal reset password!']);
		} else {
			// Mail::to($user->email)->send(new ResetPasswordNotify($user));
			return redirect('/fakultas_lembaga/user')->with(['success' => 'Password pengguna "'.$user->no_punggung.'" berhasil di reset!']);
		}
	}

	public function deleteUser($id)
	{
		// Check if super user or not
		if($id == 1){
			return response()->json(['errors' => [0 => 'Cannot delete panziswil!']]);
		}

		$user = User::find($id);

		if (empty($user)) {
			return response()->json(['errors' => [0 => 'Data not found !']]);
		}

		if (!$user->delete()) {
			return response()->json(['errors' => [0 => 'Fail to update data']]);
		} else {
			return response()->json(['success' => 'Data is successfully updated']);
		}
	}

	public function detailTransaksi($id)
	{
		$data =             DB::table('transaksi')
                            ->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
                            ->join('paketzakat','paketzakat.id','=','detail_transaksi.id_paket_zakat')
                            ->join('lembaga','lembaga.id','=','transaksi.id_lembaga')
                            ->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')
                            ->join('status_transaksi','transaksi.id','=','status_transaksi.id_transaksi')
                            ->join('donatur','donatur.id','=','transaksi.id_donatur')
                            ->leftJoin('barang','barang.id_transaksi','=','transaksi.id')
                            ->select('transaksi.id', 'donatur.nama as donatur', 'lembaga.nama_lembaga as lembaga', 'barang.nama_barang', 'transaksi.rek_bank', 'transaksi.bukti_transaksi', 'transaksi.keterangan', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'), DB::raw('group_concat(paketzakat.nama_paket_zakat SEPARATOR ", ") as paket'), DB::raw('DATE_FORMAT(transaksi.tanggal_transfer, "%d-%m-%Y") as tanggal_transfer'), 'status_transaksi.panzisda_status','jenis_transaksi.jenis_transaksi', DB::raw('COUNT(paketzakat.nama_paket_zakat) as jumlah_paket'), 'status_transaksi.komentar')
                            ->where('transaksi.id', $id)
                            ->groupBy('transaksi.id','donatur.nama','jenis_transaksi.jenis_transaksi','lembaga.nama_lembaga','barang.nama_barang','transaksi.rek_bank','transaksi.bukti_transaksi','transaksi.keterangan', 'transaksi.tanggal_transfer', 'status_transaksi.panzisda_status', 'status_transaksi.komentar')
                            ->first();

        $data->detail =     DB::table('transaksi')
                            ->where('transaksi.id', $data->id)
                            ->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
                            ->join('paketzakat','detail_transaksi.id_paket_zakat','=','paketzakat.id')
                            ->select('paketzakat.nama_paket_zakat', 'detail_transaksi.jumlah')
                            ->get();

		return json_encode($data);
	}

	public function getGroup()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.group', compact('data'));
	}

	public function getDataGroup()
	{
		$group       = Group::all();

		return DataTables::of($group)
			->addIndexColumn()
			->addColumn('aksi', function($group) {
				$button = '<center><button type="button" name="edit" id="'.$group->id.'" class="edit btn btn-secondary btn-xs"><i class="fa fa-edit"></i></button>&nbsp;<button type="button" name="delete" id="'.$group->id.'" class="delete btn btn-danger btn-xs"><i class="fa fa-trash-o"></i></button></center>';
				return $button;
			})
			->editColumn('target', function($group){
				return format_uang_with_rp($group->target);
			})
			->rawColumns(['aksi'])
			->make(true);
	}

	public function editGroup($id)
	{
		$group = Group::find($id);

		return json_encode($group);
	}

	public function updateGroup(Request $request)
	{
		// dd($request->all());
		$group = Group::find($request->id);
		if (empty($group)) {
			return response()->json(['errors' => [0 => 'Data not found !']]);
		}
		$group->target = str_replace('.', '', $request->target);
		$group->save();

		return response()->json(['success' => 'update success stored!']);
	}

	public function getDonatur()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.donatur', compact('data'));
	}

	public function getDataDonatur()
	{
		$donatur = DB::table('donatur')->leftJoin('transaksi','transaksi.id_donatur','=','donatur.id')->leftJoin('users','users.id','=','transaksi.id_users')->select('donatur.id','donatur.nama','donatur.npwp','donatur.alamat','donatur.email','donatur.no_hp')->where('users.id_wilayah', Auth::user()->id_wilayah)->orWhereNull('users.id_wilayah')->groupBy('donatur.id','donatur.nama','donatur.npwp','donatur.alamat','donatur.email','donatur.no_hp')->orderBy('donatur.nama', 'ASC')->get();

		return DataTables::of($donatur)
		->addIndexColumn()
		->addColumn('aksi', function($donatur) {
			$button = '<center><button type="button" name="detail" id="'.$donatur->id.'" class="detail btn btn-secondary btn-xs">Detail</button></center>';
			return $button;
		})
		->rawColumns(['aksi'])
		->make(true);
	}

	public function detailDonatur($id)
	{
		$data = Donatur::find($id);

		return json_encode($data);
	}

	public function editProfil()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['editUser'] = User::find(Auth::user()->id);

		return view('admin.panzisda.profil', compact('data'));
	}

	public function updateProfil(Request $request)
	{
		$user = User::find($request->id);
		$user->nama = $request->nama;
		$user->alamat = $request->alamat;
		$user->npwp = $request->npwp;
		$user->no_hp = $request->no_hp;
		$user->email = $request->email;

		if($request->new_password != NULL or $request->new_password != '') {
			$user->password = Hash::make($request->new_password);
		} else {
			$user->password = $request->password;
		}
		$user->save();
	}

	public function getDutaDonatur()
	{
		$data['user']       = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']  = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_lazis']      = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['user_panzisda']         = $this->user_panzisda->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.asduta.donatur', compact('data'));
	}

	public function getDataDonaturPerencanaan()
	{
		$donatur        = Donatur::orderBy('nama', 'ASC')->get();

		return DataTables::of($donatur)
		->addIndexColumn()
		->addColumn('aksi', function($donatur) {
			$button = '<center><button type="button" name="detail" id="'.$donatur->id.'" class="detail btn btn-secondary btn-sm">Detail</button>&nbsp;<button type="button" name="rencana" id="'.$donatur->id.'" class="rencana btn btn-info btn-sm"><i class="fa fa-plus"></i></button></center>';
			return $button;
		})
		->rawColumns(['aksi'])
		->make(true);
	}

	public function createPlan($id)
	{
		$data = Donatur::find($id);

		return json_encode($data);
	}

	public function savePlan(Request $request)
	{
		$rencana = new Perencanaan();
		$rencana->id_duta = Auth::user()->id;
		$rencana->id_donatur = $request->id_donatur;
		$rencana->save();

		return response()->json(['success' => 'update success stored!']);
	}

	public function deletePlan($id)
	{
		DB::table('perencanaan')->where('id',$id)->delete();
		return redirect()->back();
	}

	public function getLaporanDZ()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.laporan_dz', compact('data'));
	}

	public function getLaporanRealisasi()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.laporan_realisasi', compact('data'));
	}

	public function getLaporanRekonsiliasi()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.laporan_rekonsiliasi', compact('data'));
	}

	public function getLaporanValidasi()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		return view('admin.panzisda.laporan_validasi', compact('data'));
	}

	public function getLaporanRealisasiPaketZiswaf()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['lembaga']            = DB::table('lembaga')->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')->select('lembaga.nama_lembaga')->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)->orWhere('lembaga_khusus.id_lembaga', NULL)->orderBy('lembaga.id', 'ASC')->get();
		$data['jumlah_lembaga']     = count($data['lembaga']);

		return view('admin.panzisda.laporan_realisasi_paket_ziswaf', compact('data'));
	}

	public function getLaporanRealisasiDutaZakat()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['lembaga']            = DB::table('lembaga')->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')->select('lembaga.nama_lembaga')->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)->orWhere('lembaga_khusus.id_lembaga', NULL)->orderBy('lembaga.id', 'ASC')->get();
		$data['jumlah_lembaga']     = count($data['lembaga']);

		return view('admin.panzisda.laporan_realisasi_dutazakat', compact('data'));
	}

	public function getDataLaporanDZ()
	{
		$duta = DB::table('users')->whereNull('users.deleted_at')->join('role','role.id_users','=','users.id')->whereNotIn('role.id_jabatan', [1, 2, 3, 6])->join('wilayah','wilayah.id','=','users.id_wilayah')->select('users.id', 'users.no_punggung', 'users.nama', 'wilayah.nama_wilayah', DB::raw('group_concat(role.id_jabatan SEPARATOR ",") as id_jabatan'), DB::raw('group_concat(IF(role.id_atasan IS NULL, "null", role.id_atasan)) as id_atasan'))->where('users.id_wilayah', Auth::user()->id_wilayah)->groupBy('users.id','users.no_punggung','users.nama','wilayah.nama_wilayah')->orderBy('users.id_wilayah', 'ASC')->orderBy('users.nama', 'ASC')->get();

		$tmp = [];
		foreach ($duta as $item) {
			$dummy['no_punggung'] = $item->no_punggung;
			$dummy['duta_zakat'] = $item->nama;

			$jabatan = explode(',', $item->id_jabatan);
			$atasan = explode(',', $item->id_atasan);

			if (count($jabatan) == 0) {
				array_push($jabatan, "null");
				array_push($atasan, "null");
			}

			$dummy['manajer_group'] = '';
			$dummy['manajer_area'] = '';

			for ($i=0;$i<count($jabatan);$i++) {
				if ($jabatan[$i] == "5") {
					if ($atasan[$i] == "null") {
						$dummy['manajer_group'] = '';
					} else {
						$temp = User::where('id', $atasan[$i])->first();
						if($temp == null) {
							$dummy['manajer_group'] = '';
						} else {
							$dummy['manajer_group'] = $temp->nama;

							$role = DB::table('role')->where('role.id_users', $temp->id)->where('role.id_jabatan', 4)->first();

							if($role == null) {
								$dummy['manajer_area'] = null;
							} else {
								$dummy['manajer_area'] = User::where('id', $role->id_atasan)->pluck('nama');
							}
						}
					}
				} else if ($jabatan[$i] == "4") {
					if ($atasan[$i] == "null") {
						$dummy['manajer_area'] = '';
					} else {
						$temp = User::where('id', $atasan[$i])->first();
						if($temp == null) {
							$dummy['manajer_area'] = '';
						} else {
							$dummy['manajer_area'] = $temp->nama;
						}
					}
				}
			}

			$dummy['wilayah'] = $item->nama_wilayah;
			$tmp[] = $dummy;
		}

		$user = collect($tmp);

		return DataTables::of($user)
			->addIndexColumn()
			->make(true);
	}

	public function getDataLaporanRekonsiliasi()
	{
		$data = DB::table('transaksi')->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')->join('paketzakat','paketzakat.id','=','detail_transaksi.id_paket_zakat')->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')->where('status_transaksi.lazis_status', '!=', NULL)->join('users','users.id','=','transaksi.id_users')->where('users.id_wilayah', Auth::user()->id_wilayah)->where('users.deleted_at', NULL)->join('lembaga','lembaga.id','=','transaksi.id_lembaga')->join('wilayah','wilayah.id','=','users.id_wilayah')->join('jenis_transaksi','jenis_transaksi.id','=','transaksi.id_jenis_transaksi')->select('wilayah.nama_wilayah','lembaga.nama_lembaga','jenis_transaksi.jenis_transaksi','transaksi.rek_bank','users.no_punggung','paketzakat.nama_paket_zakat','transaksi.tanggal_transfer','transaksi.keterangan', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))->groupBy('wilayah.nama_wilayah','lembaga.nama_lembaga','jenis_transaksi.jenis_transaksi','transaksi.rek_bank','users.no_punggung','paketzakat.nama_paket_zakat','transaksi.tanggal_transfer','transaksi.keterangan')->get();

		return DataTables::of($data)
			->addIndexColumn()
			->editColumn('nama_lembaga', function($data){
				return strtoupper($data->nama_lembaga);
			})
			->editColumn('tanggal_transfer', function($data){
				return date('d/m/Y', strtotime($data->tanggal_transfer));
			})
			->make(true);
	}

	public function getDataLaporanRealisasi()
	{
		$duta  = DB::table('users')
				->where('users.deleted_at', NULL)
				->where('users.id_wilayah', Auth::user()->id_wilayah)
				->join('role','role.id_users','=','users.id')
				->join('group','group.id','=','role.id_group')
				->join('wilayah','wilayah.id','=','users.id_wilayah')
				->where('role.id_jabatan', 5)
				->select('users.id', 'users.no_punggung', 'users.nama', 'wilayah.nama_wilayah','role.id_atasan as manajer_group', DB::raw('CAST(SUM(group.target) as UNSIGNED) as target'))
				->groupBy('users.id', 'users.nama', 'wilayah.nama_wilayah', 'role.id_atasan', 'users.no_punggung')
				->get();

		foreach($duta as $item) {
			$manajer = User::where('id', $item->manajer_group)->first();
			$item->manajer_group = $manajer->nama;

			$realisasi  = DB::table('users')
						->where('users.id', $item->id)
						->join('role', 'role.id_users','=','users.id')
						->where('role.id_jabatan', 5)
						->join('transaksi','users.id','=','transaksi.id_users')
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('status_transaksi','transaksi.id','=','status_transaksi.id_transaksi')
						->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
						->where('status_transaksi.lazis_status', '!=', NULL)
						->first();

			$item->realisasi = ($realisasi->jumlah != NULL) ? $realisasi->jumlah : 0;
			$item->persentase = ($realisasi->jumlah != NULL && $item->target != 0) ? number_format(($realisasi->jumlah / $item->target) * 100, 2) : 0;
		}
		$data = $duta->sortbyDesc('realisasi');

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function getDataLaporanValidasi()
	{
		$manajer    = DB::table('users')
					->where('users.deleted_at', NULL)
					->where('users.id_wilayah', Auth::user()->id_wilayah)
					->join('role', 'role.id_users','=','users.id')
					->select('users.id', 'users.no_punggung', 'users.nama')
					->where('role.id_jabatan', 4)
					->get();

		$dummy   = [];
		foreach($manajer as $value) {
			$target     = DB::table('users')
						->where('users.deleted_at', NULL)
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->join('role','role.id_users','=','users.id')
						->where('role.id_atasan', $value->id)
						->join('group','group.id','=','role.id_group')
						->select(DB::raw('CAST(SUM(group.target) as UNSIGNED) as target'))
						->first();

			$transaksi  = DB::table('users')
						->where('users.deleted_at', NULL)
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->join('role','role.id_users','=','users.id')
						->where('role.id_atasan', $value->id)
						->where('role.id_jabatan', 5)
						->join('transaksi','transaksi.id_users','=','users.id')
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
						->select('status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'status_transaksi.lazis_status', DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
						->groupBy('status_transaksi.manajer_status', 'status_transaksi.panzisda_status', 'status_transaksi.lazis_status')
						->get();
			// $tmp[] = $transaksi;

			$tmp['no_punggung'] = $value->no_punggung;
			$tmp['nama']        = $value->nama;
			$tmp['target']      = ($target->target != 0) ? $target->target : 0;

			$valid_mg = 0;
			$valid_pz = 0;
			$valid_lz = 0;
			$total = 0;
			foreach($transaksi as $item) {
				if ($item->manajer_status != NULL and $item->panzisda_status != NULL and $item->lazis_status != NULL) {
					$valid_lz = $valid_lz + $item->jumlah;
					$valid_pz = $valid_pz + $item->jumlah;
					$valid_mg = $valid_mg + $item->jumlah;
				} else if ($item->manajer_status != NULL and $item->panzisda_status != NULL) {
					$valid_pz = $valid_pz + $item->jumlah;
					$valid_mg = $valid_mg + $item->jumlah;
				} else {
					$valid_mg = $valid_mg + $item->jumlah;
				}

				$total = $total + $item->jumlah;
			}

			$tmp['total']       = $total;
			$tmp['valid_mg']    = $valid_mg;
			$tmp['valid_pz']    = $valid_pz;
			$tmp['valid_lz']    = $valid_lz;
			$tmp['persentase']  = ($valid_lz != 0 && $target->target != 0) ? number_format(($valid_lz / $target->target) * 100, 2) : 0;
			$dummy[]            = $tmp;
		}
		$data = collect($dummy);

		$data = $data->sortByDesc('total');

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function getDataLaporanRealisasiPaketZiswaf()
	{
		$paket      = PaketZakat::all();
		$lembaga    = DB::table('lembaga')
					->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')
					->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')
					->select('lembaga.*')
					->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)
					->orWhere('lembaga_khusus.id_lembaga', NULL)
					->orderBy('lembaga.id', 'ASC')
					->get();

		$temp = [];
		$hitung = 1;

		foreach($paket as $item1) {
			$dummy['no']    = $hitung;
			$dummy['paket'] = $item1->nama_paket_zakat;

			$count = 1;
			foreach($lembaga as $item2) {
				$transaksi   = DB::table('transaksi')
						->leftJoin('users','users.id','=','transaksi.id_users')
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
						->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
						->where('detail_transaksi.id_paket_zakat', $item1->id)
						->where('transaksi.id_lembaga', $item2->id)
						->where('status_transaksi.lazis_status', '!=', NULL)
						->first();

				if ($transaksi->jumlah == NULL) {
					$transaksi->jumlah = 0;
				}
				$dummy['lembaga_'.$count] = $transaksi->jumlah;
				$count = $count+1;
			}
			$temp[] = $dummy;
			$hitung = $hitung + 1;
		}
		$data = $temp;

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function getDataLaporanRealisasiDutaZakat()
	{
		$paket      = PaketZakat::all();
		$lembaga    = DB::table('lembaga')
					->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')
					->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')
					->select('lembaga.*')
					->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)
					->orWhere('lembaga_khusus.id_lembaga', NULL)
					->orderBy('lembaga.id', 'ASC')
					->get();

		$temp = [];

		foreach($paket as $item1) {
			$dummy['paket'] = $item1->nama_paket_zakat;

			$count = 1;
			$total = 0;
			foreach($lembaga as $item2) {

				$transaksi   = DB::table('transaksi')
							->leftJoin('users','users.id','=','transaksi.id_users')
							->where('users.id_wilayah', Auth::user()->id_wilayah)
							->where('users.deleted_at', NULL)
							->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
							->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
							->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
							->where('detail_transaksi.id_paket_zakat', $item1->id)
							->where('transaksi.id_lembaga', $item2->id)
							->first();

				if ($transaksi->jumlah == NULL) {
					$transaksi->jumlah = 0;
				}
				$dummy['lembaga_'.$count] = $transaksi->jumlah;
				$total = $total + $dummy['lembaga_'.$count];
				$count = $count+1;
			}
			$dummy['jumlah'] = $total;
			$temp[] = $dummy;
		}
		$data = $temp;

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function getLaporanDistribusi()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();
		$data['lembaga']            = DB::table('lembaga')->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')->select('lembaga.nama_lembaga')->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)->orWhere('lembaga_khusus.id_lembaga', NULL)->orderBy('lembaga.id', 'ASC')->get();
		$data['jumlah_lembaga']     = count($data['lembaga']);

		return view('admin.panzisda.laporan_distribusi', compact('data'));
	}

	public function getDataLaporanDistribusi()
	{
		$paket      = DB::table('paketzakat')->join('distribusi', 'distribusi.id_paket_zakat','=','paketzakat.id')->select('paketzakat.*')->orderBy('paketzakat.id', 'ASC')->get();
		$lembaga    = DB::table('lembaga')
					->leftJoin('lembaga_khusus','lembaga.id','=','lembaga_khusus.id_lembaga')
					->leftJoin('wilayah','wilayah.id','=','lembaga_khusus.id_wilayah')
					->select('lembaga.*')
					->where('lembaga_khusus.id_wilayah', Auth::user()->id_wilayah)
					->orWhere('lembaga_khusus.id_lembaga', NULL)
					->orderBy('lembaga.id', 'ASC')
					->get();

		$temp = [];
		foreach($paket as $item1) {
			$dummy['paket'] = $item1->nama_paket_zakat;
			$jumlah     = DB::table('transaksi')
						->leftJoin('users','users.id','=','transaksi.id_users')
						->where('users.id_wilayah', Auth::user()->id_wilayah)
						->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
						->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
						->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
						->where('detail_transaksi.id_paket_zakat', $item1->id)
						->where('transaksi.id_lembaga', '!=', function($query) {
							$query->select('id')->from('lembaga')->whereIn('nama_lembaga', ['dana mandiri', 'Dana Mandiri', 'DANA MANDIRI'])->pluck('id');
						})
						->where('status_transaksi.lazis_status', '!=', NULL)
						->first();

			$distribusi     = Distribusi::where('id_paket_zakat', $item1->id)->get();

			foreach($distribusi as $dis) {
				$dummy['panzisnas'] = ($jumlah->jumlah != NULL) ? round(($dis->panzisnas * $jumlah->jumlah) / 100) : 0;
				$dummy['panziswil'] = ($jumlah->jumlah != NULL) ? round(($dis->panziswil * $jumlah->jumlah) / 100) : 0;
				$dummy['panzisda'] = ($jumlah->jumlah != NULL) ? round(($dis->panzisda * $jumlah->jumlah) / 100) : 0;

				$total = 0;
				foreach($lembaga as $item2) {
					$name = '';
					if ($item2->nama_lembaga == 'IZI' OR $item2->nama_lembaga == 'izi' or $item2->nama_lembaga == 'Izi') {
						$name = 'izi';
					} else if ($item2->nama_lembaga == 'LAZDAI' OR $item2->nama_lembaga == 'lazdai' or $item2->nama_lembaga == 'Lazdai') {
						$name = 'lazdai';
					} else if ($item2->nama_lembaga == 'DANA MANDIRI' OR $item2->nama_lembaga == 'dana mandiri' or $item2->nama_lembaga == 'Dana Mandiri') {
						$name = 'dana_mandiri';
					} else {
						// $name = 'yayasan';
						$name = 'upz';
					}

					$transaksi  = DB::table('transaksi')
								->leftJoin('users','users.id','=','transaksi.id_users')
								->where('users.id_wilayah', Auth::user()->id_wilayah)
								->join('detail_transaksi','detail_transaksi.id_transaksi','=','transaksi.id')
								->join('status_transaksi','status_transaksi.id_transaksi','=','transaksi.id')
								->select(DB::raw('CAST(SUM(detail_transaksi.jumlah) as UNSIGNED) as jumlah'))
								->where('detail_transaksi.id_paket_zakat', $item1->id)
								->where('transaksi.id_lembaga', $item2->id)
								->where('status_transaksi.lazis_status', '!=', NULL)
								->first();

					if ($name == 'dana_mandiri') {
						$dummy[$name] = ($transaksi->jumlah != NULL) ? $transaksi->jumlah : 0;
					} else {
						$dummy[$name] = ($transaksi->jumlah != NULL) ? round(($dis->mitra_strategis * $transaksi->jumlah) / 100) : 0;
					}
					$total = $total + $dummy[$name];
				}
				if(empty($dummy['yayasan'])) {
					$dummy['yayasan'] = 0;
				}
			}
			$dummy['jumlah'] = $dummy['panzisnas'] + $dummy['panziswil'] + $dummy['panzisda'] + $total;
			$temp[] = $dummy;
		}
		$data = $temp;

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function getLaporanDonatur()
	{
		$data['user']               = $this->user->where('id', Auth::user()->id)->first();
		$data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
		$data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
		$data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
		$data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
		$data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
		$data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();

		return view('admin.panzisda.laporan_donatur', compact('data'));
	}

	public function getDataLaporanDonatur()
	{
		$data = 	DB::table('donatur')
					->leftJoin('transaksi','transaksi.id_donatur','=','donatur.id')
					->leftJoin('users','users.id','=','transaksi.id_users')
					->select('donatur.*')
					->whereIn('donatur.nama', function($query) {
						$query->select('nama')->from('users')->where('id_wilayah', Auth::user()->id_wilayah)->get();
					})
					->orWhereIn('transaksi.id_users', function($query) {
						$query->select('id')->from('users')->where('id_wilayah', Auth::user()->id_wilayah)->get();
					})
					->orderBy('donatur.id', 'ASC')
					->get();
		$data = $data->unique('id_donatur');

		return DataTables::of($data)
			->addIndexColumn()
			->make(true);
	}

	public function kurbanIndex()
	{
		$data['user']       			= $this->user->where('id', Auth::user()->id)->first();
        $data['user_duta']  			= $this->user_duta->where('id', Auth::user()->id)->first();
        $data['user_manajer']       	= $this->user_manajer->where('id', Auth::user()->id)->first();
        $data['user_manajerarea']   	= $this->user_manajerarea->where('id', Auth::user()->id)->first();
        $data['user_panzisda']      	= $this->user_panzisda->where('id', Auth::user()->id)->first();
        $data['user_lazis']         	= $this->user_lazis->where('id', Auth::user()->id)->first();
        $data['user_panziswil']         = $this->user_panziswil->where('id', Auth::user()->id)->first();

		$manajer	= DB::table('users')
					->where('users.id_wilayah', Auth::user()->id_wilayah)
					->whereNull('deleted_at')
					->join('role','role.id_users','=','users.id')
					->where('role.id_jabatan', 4)
					->select('users.id','users.no_punggung','users.nama')
					->get();

        $tmp = [];

        foreach ($manajer as $item) {
			$duta			= Role::where('id_jabatan', 5)->where('id_atasan', $item->id)->pluck('id_users');

			$y				= DB::table('transaksi_kurban as transaksi')
							->whereIn('transaksi.id_users', $duta)
							->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
							->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
							->select(DB::raw('group_concat(detail.nama separator ";") as y'))
							->whereNotNull('status.panzisda_status')
							->first();

            $kambing        = DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Kambing/Domba')
                            ->whereNotNull('status.panziswil_status')
                            ->first();

            $sapi           = DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Sapi/Kerbau')
                            ->whereNotNull('status.panziswil_status')
                            ->first();


			$dummy['target'] 		= count($duta);
            $dummy['name'] 			= $item->no_punggung;
			$dummy['y'] 			= ($y->y != NULL) ? count(explode(";", $y->y)) : 0;
			$dummy['kambing']		= ($kambing->y != NULL) ? $kambing->y : 0;
			$dummy['sapi']			= ($sapi->y != NULL) ? $sapi->y : 0;
			$dummy['persentase'] 	= ($dummy['y'] != 0 && $dummy['target'] != 0) ? number_format(($dummy['y'] / $dummy['target']) * 100, 2) : 0;
			$dummy['drilldown']     = $item->no_punggung;
            $tmp[] 					= $dummy;
        }
        $data['manajer'] = $tmp;

		//DUTA
		$tmp2 = [];
		foreach ($manajer as $value) {
			$dummys['name'] = $value->nama;
			$dummys['id']   = $value->no_punggung;

			$atasan         = Role::where('id_jabatan', 5)->where('id_atasan', $value->id)->pluck('id_users');
			$duta			= User::whereIn('id', $atasan)->get();

			$tmp3 = [];
			foreach($duta as $value1) {
				$y			= DB::table('transaksi_kurban as transaksi')
							->where('transaksi.id_users', $value1->id)
							->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
							->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
							->select(DB::raw('group_concat(detail.nama separator ";") as y'))
							->whereNotNull('status.panzisda_status')
							->first();
				$kambing    = DB::table('transaksi_kurban as transaksi')
                            ->where('transaksi.id_users', $value1->id)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Kambing/Domba')
                            ->whereNotNull('status.panziswil_status')
                            ->first();

            	$sapi       = DB::table('transaksi_kurban as transaksi')
                            ->where('transaksi.id_users', $value1->id)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Sapi/Kerbau')
                            ->whereNotNull('status.panziswil_status')
                            ->first();

				$dummyss['target'] 		= 1;
				$dummyss['name'] 		= $value1->no_punggung;
				$dummyss['y'] 			= ($y->y != NULL) ? count(explode(";", $y->y)) : 0;
				$dummyss['kambing']		= ($kambing->y != NULL) ? $kambing->y : 0;
				$dummyss['sapi']		= ($sapi->y != NULL) ? $sapi->y : 0;
				$dummyss['persentase'] 	= ($dummyss['y'] != 0 && $dummyss['target'] != 0) ? number_format(($dummyss['y'] / $dummyss['target']) * 100, 2) : 0;
				$tmp3[] = $dummyss;
			}

			$dummys['data']	= $tmp3;
			$tmp2[]			= $dummys;
		}
		$data['duta'] = $tmp2;

		//Realisasi
        $panzisda   = User::where('id', Auth::user()->id)->get();
        $tmp_ = [];
        foreach ($panzisda as $item_) {
			$jabatan		= Role::where('id_jabatan', 5)->pluck('id_users');
            $duta           = User::whereIn('id', $jabatan)->whereNull('deleted_at')->where('id_wilayah', Auth::user()->id_wilayah)->pluck('id');

			$y				= DB::table('transaksi_kurban as transaksi')
							->whereIn('transaksi.id_users', $duta)
							->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
							->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
							->select(DB::raw('group_concat(detail.nama separator ";") as y'))
							->whereNotNull('status.panzisda_status')
							->first();

            $kambing        = DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Kambing/Domba')
                            ->whereNotNull('status.panziswil_status')
                            ->first();

            $sapi           = DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', 'Sapi/Kerbau')
                            ->whereNotNull('status.panziswil_status')
                            ->first();

			$dummy_['target'] 		= count($duta);
            $dummy_['name'] 		= $item_->nama;
			$dummy_['y'] 			= ($y->y != NULL) ? count(explode(";", $y->y)) : 0;
			$dummy_['kambing']		= ($kambing->y != NULL) ? $kambing->y : 0;
			$dummy_['sapi']			= ($sapi->y != NULL) ? $sapi->y : 0;
			$dummy_['persentase'] 	= ($dummy_['y'] != 0 && $dummy_['target'] != 0) ? number_format(($dummy_['y'] / $dummy_['target']) * 100, 2) : 0;
            $tmp_[] 				= $dummy_;
        }
        $data['realisasi'] = $tmp_;

		//Persentase
		$tmp__ = [];
		$jenis = ['Kambing/Domba', 'Sapi/Kerbau'];
        foreach ($jenis as $item__) {
			$jabatan		= Role::where('id_jabatan', 5)->pluck('id_users');
            $duta           = User::whereIn('id', $jabatan)->whereNull('deleted_at')->where('id_wilayah', Auth::user()->id_wilayah)->pluck('id');

			$transaksi       = DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'))
                            ->where('detail.jenis', $item__)
                            ->first();

            $y        		= DB::table('transaksi_kurban as transaksi')
                            ->whereIn('transaksi.id_users', $duta)
                            ->join('detail_transaksi_kurban as detail','detail.id_transaksi','=','transaksi.id')
                            ->join('status_transaksi_kurban as status','status.id_transaksi','=','transaksi.id')
                            ->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as y'), DB::raw('group_concat(detail.nama separator ";") as pekurban'))
                            ->where('detail.jenis', $item__)
                            ->whereNotNull('status.panziswil_status')
                            ->first();

            $dummy__['name'] 		= $item__;
			$dummy__['y'] 			= ($y->y != NULL) ? $y->y : 0;
			$dummy__['pekurban'] 	= ($y->pekurban != NULL) ? count(explode(";", $y->pekurban)) : 0;
			$dummy__['transaksi'] 	= ($transaksi->y != NULL) ? $transaksi->y : 0;
			$dummy__['persentase'] 	= ($dummy__['y'] != 0 && $dummy__['transaksi'] != 0) ? number_format(($dummy__['y'] / $dummy__['transaksi']) * 100, 2) : 0;
            $tmp__[] 				= $dummy__;
        }
        $data['persentase'] = $tmp__;

        return view('admin.panzisda.beranda_kurban', compact('data'));
	}

	public function kurban()
    {
        $data['user']               = $this->user->where('id', Auth::user()->id)->first();
        $data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
        $data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
        $data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
        $data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
        $data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
        $data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();

        return view('admin.panzisda.kurban', compact('data'));
    }

    public function getDataKurban($id)
    {
        if($id == 0) {
        	$transaksi = DB::table('transaksi_kurban AS transaksi')
			->whereIn('transaksi.id_users', function($request){
				$request->select('id')->from('users')
				->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
			})
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at AS update',
                DB::raw('group_concat(detail.jenis separator ", ") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ", ") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ", ") AS nama'))
			->orderBy('status.panzisda_status', 'ASC')
			->orderBy('transaksi.tgl_transaksi', 'DESC')
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at')
            ->get();
        } else if ($id == 1) {
        	$transaksi = DB::table('transaksi_kurban AS transaksi')
			->whereIn('transaksi.id_users', function($request){
				$request->select('id')->from('users')
				->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
			})
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at AS update',
                DB::raw('group_concat(detail.jenis separator ", ") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ", ") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ", ") AS nama'))
            ->whereNotNull('status.panzisda_status')
			->orderBy('status.panzisda_status', 'ASC')
			->orderBy('transaksi.tgl_transaksi', 'DESC')
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at')
            ->get();
        } else if ($id == 2) {
        	$transaksi = DB::table('transaksi_kurban AS transaksi')
			->whereIn('transaksi.id_users', function($request){
				$request->select('id')->from('users')
				->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
			})
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at AS update',
                DB::raw('group_concat(detail.jenis separator ", ") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ", ") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ", ") AS nama'))
            ->whereNotNull('status.komentar')
			->orderBy('status.panzisda_status', 'ASC')
			->orderBy('transaksi.tgl_transaksi', 'DESC')
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at')
            ->get();
        } else if ($id == 3) {
        	$transaksi = DB::table('transaksi_kurban AS transaksi')
			->whereIn('transaksi.id_users', function($request){
				$request->select('id')->from('users')
				->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
			})
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at AS update',
                DB::raw('group_concat(detail.jenis separator ", ") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ", ") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ", ") AS nama'))
            ->whereNotNull('status.manajer_status')
            ->whereNull('status.panzisda_status')
			->orderBy('status.panzisda_status', 'ASC')
			->orderBy('transaksi.tgl_transaksi', 'DESC')
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at')
            ->get();
        } else {
        	$transaksi = DB::table('transaksi_kurban AS transaksi')
			->whereIn('transaksi.id_users', function($request){
				$request->select('id')->from('users')
				->where('id_wilayah', Auth::user()->id_wilayah)->get()->toArray();
			})
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at AS update',
                DB::raw('group_concat(detail.jenis separator ", ") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ", ") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ", ") AS nama'))
            ->whereNull('status.manajer_status')
			->orderBy('status.panzisda_status', 'ASC')
			->orderBy('transaksi.tgl_transaksi', 'DESC')
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'status.updated_at')
            ->get();
        }

        return DataTables::of($transaksi)
            ->addIndexColumn()
            ->addColumn('aksi', function($transaksi) {
                if ($transaksi->manajer_status != NULL AND $transaksi->panzisda_status == NULL) {
					$button = '<center><button type="button" name="detail" id="'.$transaksi->id.'" class="detail btn btn-secondary btn-xs">VERIFIKASI</button></center>';
				} else if ($transaksi->manajer_status == NULL) {
					$button = '<center><button type="button" name="detail" id="'.$transaksi->id.'" class="detail btn btn-secondary btn-xs" disabled>VERIFIKASI</button></center>';
				} else {
					$button = '<center><button type="button" name="detail" id="'.$transaksi->id.'" class="detail btn btn-secondary btn-xs"><i class="fa fa-eye"></i></button></center>';
				}
				return $button;
            })
            ->editColumn('panzisda_status', function($transaksi){
                if (($transaksi->manajer_status == null and $transaksi->komentar == null) or $transaksi->update != null) {
                    $text = '<b style="color:brown;">TUNGGU PROSES JURUSAN</b>';
                } else if ($transaksi->manajer_status != NULL and $transaksi->panzisda_status == null) {
                    $text = '<b style="color:brown;">TUNGGU PROSES FAKULTAS</b>';
                } else if ($transaksi->manajer_status == null and $transaksi->komentar != null) {
                    $text = '<b style="color:red;">TIDAK VALID</b>';
                } else if ($transaksi->panzisda_status != NULL) {
                    $text = '<b style="color:green;">VALID</b>';
                }
                return $text;
            })
            ->editColumn('nama', function($transaksi) {
                return implode(', ', explode(";", $transaksi->nama));
            })
            ->editColumn('tgl_transaksi', function($transaksi){
                return date('d/m/Y', strtotime($transaksi->tgl_transaksi));
            })
            ->rawColumns(['panzisda_status','aksi'])
            ->make(true);
    }

    public function detailKurban($id)
    {
        $data = DB::table('transaksi_kurban AS transaksi')
            ->join('detail_transaksi_kurban AS detail', 'detail.id_transaksi', '=','transaksi.id')
            ->join('status_transaksi_kurban AS status', 'status.id_transaksi','=','transaksi.id')
            ->select('transaksi.id', 'transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'transaksi.tgl_transaksi',
                DB::raw('group_concat(detail.jenis separator ",") AS jenis'),
                DB::raw('group_concat(detail.jumlah separator ",") AS jumlah'),
                DB::raw('group_concat(detail.nama separator ",") AS nama'))
            ->where('transaksi.id', $id)
            ->groupBy('transaksi.id','transaksi.tgl_transaksi', 'transaksi.lokasi', 'status.manajer_status', 'status.panzisda_status', 'status.panziswil_status', 'status.komentar', 'transaksi.tgl_transaksi')
            ->first();
        $data->jenis = explode(',', $data->jenis);
        $data->jumlah = explode(',', $data->jumlah);
        $data->nama = explode(',', $data->nama);

        return json_encode($data);
    }

    public function getStatusKurban($id)
	{
		$data = TransaksiKurban::find($id);

		return json_encode($data);
	}

	public function updateStatusKurban(Request $request)
	{
		if ($request->setujui == 'OK') {
			$status = StatusTransaksiKurban::where('id_transaksi', $request->idTrx1)->update([
                'panzisda_status' => Auth::user()->id,
				'panziswil_status' => 1,
                'updated_at' => null
            ]);
		} else {
			$status = StatusTransaksiKurban::where('id_transaksi', $request->idTrx2)->update([
				'manajer_status' => null,
                'panzisda_status' => null,
                'updated_at' => null,
                'komentar' => $request->komentar
            ]);
		}
	}

	public function getLaporanRincianKurban()
    {
        $data['user']               = $this->user->where('id', Auth::user()->id)->first();
        $data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
        $data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
        $data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
        $data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
        $data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
        $data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();

		$data['duta']				= User::where('id_wilayah', Auth::user()->id_wilayah)->orderBy('nama', 'ASC')->get();
        return view('admin.panzisda.laporan_rincian_kurban', compact('data'));
    }

    public function getDataLaporanRincianKurban($id)
    {
        if($id == 0)
		{
			$data  = DB::table('users')
                ->where('users.deleted_at', NULL)
                ->where('users.id_wilayah', Auth::user()->id_wilayah)
                ->join('role','role.id_users','=','users.id')
                ->where('role.id_jabatan', 5)
                ->join('transaksi_kurban as kurban', 'kurban.id_users','=','users.id')
                ->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
                ->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
                ->select('users.no_punggung', 'users.nama as dutazakat', 'kurban.tgl_transaksi', 'kurban.lokasi', 'detail.jenis', 'detail.jumlah', 'detail.nama as pekurban')
                ->whereNotNull('status.panziswil_status')
                ->get();

			foreach($data as $item) {
				$item->kambing 		= ($item->jenis == "Kambing/Domba") ? $item->jumlah : 0;
				$item->sapi 		= ($item->jenis == "Sapi/Kerbau") ? $item->jumlah : 0;
				$item->jumlah_nama	= count(explode(";", $item->pekurban));
				$item->pekurban		= implode(', ', explode(';', $item->pekurban));
			}
		} else {
			$data  = DB::table('users')
                ->where('users.deleted_at', NULL)
                ->where('users.id_wilayah', Auth::user()->id_wilayah)
                ->where('users.id', $id)
                ->join('role','role.id_users','=','users.id')
                ->where('role.id_jabatan', 5)
                ->join('transaksi_kurban as kurban', 'kurban.id_users','=','users.id')
                ->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
                ->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
                ->select('users.no_punggung', 'users.nama as dutazakat', 'kurban.tgl_transaksi', 'kurban.lokasi', 'detail.jenis', 'detail.jumlah', 'detail.nama as pekurban')
                ->whereNotNull('status.panziswil_status')
                ->get();

			foreach($data as $item) {
				$item->kambing 		= ($item->jenis == "Kambing/Domba") ? $item->jumlah : 0;
				$item->sapi 		= ($item->jenis == "Sapi/Kerbau") ? $item->jumlah : 0;
				$item->jumlah_nama	= count(explode(";", $item->pekurban));
				$item->pekurban		= implode(', ', explode(';', $item->pekurban));
			}
		}

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_transaksi', function($transaksi){
                return date('d/m/Y', strtotime($transaksi->tgl_transaksi));
            })
            ->make(true);
    }

	public function getLaporanKurban()
    {
        $data['user']               = $this->user->where('id', Auth::user()->id)->first();
        $data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
        $data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
        $data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
        $data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
        $data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
        $data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();

		$manajer					= Role::where('id_jabatan', 4)->pluck('id_users');
		$data['manajer']			= User::where('id_wilayah', Auth::user()->id_wilayah)->whereIn('id', $manajer)->whereNull('deleted_at')->orderBy('nama','asc')->get();
        return view('admin.panzisda.laporan_monitoring_kurban', compact('data'));
    }

    public function getDataLaporanKurban($id)
    {
		if($id == 0) {
			$manajer	= Role::where('id_jabatan', 4)->pluck('id_users');
			$data		= User::whereIn('id', $manajer)->where('id_wilayah', Auth::user()->id_wilayah)->whereNull('deleted_at')->select('id','nama','no_punggung')->get();

			foreach($data as $item) {
				$jabatan			= Role::where('id_jabatan', 5)->where('id_atasan', $item->id)->pluck('id_users');
				$duta				= User::whereIn('id', $jabatan)->whereNull('deleted_at')->pluck('id');
				$item->target       = count($duta);

				// $duta				= Role::where('id_atasan', $item->id)->where('id_jabatan', 5)->pluck('id_users');
				$dz					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->first();
				$sapi_dz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_dz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_dz  	= (is_null($dz->jumlah_nama)) ? 0 : count(explode(";", $dz->jumlah_nama));
				$item->sapi_dz      = (is_null($sapi_dz->jumlah)) ? 0 : $sapi_dz->jumlah;
				$item->kambing_dz   = (is_null($kambing_dz->jumlah)) ? 0 : $kambing_dz->jumlah;

				$mg					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->first();
				$sapi_mg			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_mg			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_mg  	= (is_null($mg->jumlah_nama)) ? 0 : count(explode(";", $mg->jumlah_nama));
				$item->sapi_mg      = (is_null($sapi_mg->jumlah)) ? 0 : $sapi_mg->jumlah;
				$item->kambing_mg   = (is_null($kambing_mg->jumlah)) ? 0 : $kambing_mg->jumlah;

				$pz					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->first();
				$sapi_pz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_pz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_pz  	= (is_null($pz->jumlah_nama)) ? 0 : count(explode(";", $pz->jumlah_nama));
				$item->sapi_pz      = (is_null($sapi_pz->jumlah)) ? 0 : $sapi_pz->jumlah;
				$item->kambing_pz   = (is_null($kambing_pz->jumlah)) ? 0 : $kambing_pz->jumlah;

				$item->valid   = ($item->jumlah_pz != 0) ? ($item->jumlah_pz / $item->jumlah_dz) * 100 : 0;
			}
		} else {
			$data		= User::where('id', $id)->where('id_wilayah', Auth::user()->id_wilayah)->whereNull('deleted_at')->select('id','nama','no_punggung')->get();

			foreach($data as $item) {
				$jabatan			= Role::where('id_jabatan', 5)->where('id_atasan', $item->id)->pluck('id_users');
				$duta				= User::whereIn('id', $jabatan)->whereNull('deleted_at')->pluck('id');
				$item->target       = count($duta);

				$duta				= Role::where('id_atasan', $item->id)->where('id_jabatan', 5)->pluck('id_users');
				$dz					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->first();
				$sapi_dz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_dz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_dz  	= (is_null($dz->jumlah_nama)) ? 0 : count(explode(";", $dz->jumlah_nama));
				$item->sapi_dz      = (is_null($sapi_dz->jumlah)) ? 0 : $sapi_dz->jumlah;
				$item->kambing_dz   = (is_null($kambing_dz->jumlah)) ? 0 : $kambing_dz->jumlah;

				$mg					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->first();
				$sapi_mg			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_mg			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.manajer_status')
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_mg  	= (is_null($mg->jumlah_nama)) ? 0 : count(explode(";", $mg->jumlah_nama));
				$item->sapi_mg      = (is_null($sapi_mg->jumlah)) ? 0 : $sapi_mg->jumlah;
				$item->kambing_mg   = (is_null($kambing_mg->jumlah)) ? 0 : $kambing_mg->jumlah;

				$pz					= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->first();
				$sapi_pz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->where('detail.jenis', 'Sapi/Kerbau')
									->first();
				$kambing_pz			= DB::table('transaksi_kurban as kurban')
									->whereIn('kurban.id_users', $duta)
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereNotNull('status.panzisda_status')
									->where('detail.jenis', 'Kambing/Domba')
									->first();

				$item->jumlah_pz  	= (is_null($pz->jumlah_nama)) ? 0 : count(explode(";", $pz->jumlah_nama));
				$item->sapi_pz      = (is_null($sapi_pz->jumlah)) ? 0 : $sapi_pz->jumlah;
				$item->kambing_pz   = (is_null($kambing_pz->jumlah)) ? 0 : $kambing_pz->jumlah;

				$item->valid   = ($item->jumlah_pz != 0) ? ($item->jumlah_pz / $item->jumlah_dz) * 100 : 0;
			}
		}

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

	public function getLaporanRealisasiManajer()
    {
        $data['user']               = $this->user->where('id', Auth::user()->id)->first();
        $data['user_duta']          = $this->user_duta->where('id', Auth::user()->id)->first();
        $data['user_manajer']       = $this->user_manajer->where('id', Auth::user()->id)->first();
        $data['user_manajerarea']   = $this->user_manajerarea->where('id', Auth::user()->id)->first();
        $data['user_panzisda']      = $this->user_panzisda->where('id', Auth::user()->id)->first();
        $data['user_lazis']         = $this->user_lazis->where('id', Auth::user()->id)->first();
        $data['user_panziswil']     = $this->user_panziswil->where('id', Auth::user()->id)->first();

		$manajer					= Role::where('id_jabatan', 4)->pluck('id_users');
		$data['duta']				= User::whereIn('id', $manajer)->where('id_wilayah', Auth::user()->id_wilayah)->orderBy('nama', 'ASC')->get();
        return view('admin.panzisda.laporan_manajer_kurban', compact('data'));
    }

    public function getDataLaporanRealisasiManajer($id)
    {
        if($id == 0)
		{
			$data	= DB::table('users')
				->where('id_wilayah', Auth::user()->id_wilayah)
				->whereNull('deleted_at')
				->join('role','role.id_users','=','users.id')
				->where('role.id_jabatan', 5)
				->select('users.id','users.nama','users.no_punggung','role.id_atasan')
				->orderBy('users.nama', 'asc')
				->get();

			foreach($data as $item) {
				$item->target       = 1;
				$transaksi          = TransaksiKurban::where('id_users', $item->id)->pluck('id');
				$manajer			= User::find($item->id_atasan);
				$item->manajer		= $manajer->nama;

				$temp               = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereIn('kurban.id', $transaksi)
									->whereNotNull('status.panziswil_status')
									->first();

				$sapi               = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw("IFNULL(SUM(detail.jumlah), 0) as jumlah"))
									->whereIn('kurban.id', $transaksi)
									->where('detail.jenis', 'Sapi/Kerbau')
									->whereNotNull('status.panziswil_status')
									->first();

				$kambing            = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw("IFNULL(SUM(detail.jumlah), 0) as jumlah"))
									->whereIn('kurban.id', $transaksi)
									->where('detail.jenis', 'Kambing/Domba')
									->whereNotNull('status.panziswil_status')
									->first();

				$item->jumlah_nama  = (is_null($temp->jumlah_nama)) ? 0 : count(explode(";", $temp->jumlah_nama));
				$item->jumlah       = (is_null($temp->jumlah)) ? 0 : $temp->jumlah;
				$item->sapi         = (is_null($sapi->jumlah)) ? 0 : $sapi->jumlah;
				$item->kambing      = (is_null($kambing->jumlah)) ? 0 : $kambing->jumlah;
				$item->persentase   = ($item->jumlah_nama != 0 && $item->target != 0) ? ($item->jumlah_nama / $item->target) * 100 : 0;
			}
		} else {
			$data	= DB::table('users')
				->where('id_wilayah', Auth::user()->id_wilayah)
				->whereNull('deleted_at')
				->join('role','role.id_users','=','users.id')
				->where('role.id_jabatan', 5)
				->where('role.id_atasan', $id)
				->select('users.id','users.nama','users.no_punggung','role.id_atasan')
				->orderBy('users.nama', 'asc')
				->get();

			foreach($data as $item) {
				$item->target       = 1;
				$transaksi          = TransaksiKurban::where('id_users', $item->id)->pluck('id');
				$manajer			= User::find($item->id_atasan);
				$item->manajer		= $manajer->nama;

				$temp               = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw('group_concat(detail.nama separator ";") as jumlah_nama'), DB::raw('CAST(SUM(detail.jumlah) as unsigned) as jumlah'))
									->whereIn('kurban.id', $transaksi)
									->whereNotNull('status.panziswil_status')
									->first();

				$sapi               = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw("IFNULL(SUM(detail.jumlah), 0) as jumlah"))
									->whereIn('kurban.id', $transaksi)
									->where('detail.jenis', 'Sapi/Kerbau')
									->whereNotNull('status.panziswil_status')
									->first();

				$kambing            = DB::table('transaksi_kurban as kurban')
									->join('detail_transaksi_kurban as detail', 'detail.id_transaksi','=','kurban.id')
									->join('status_transaksi_kurban as status', 'status.id_transaksi','=','kurban.id')
									->select(DB::raw("IFNULL(SUM(detail.jumlah), 0) as jumlah"))
									->whereIn('kurban.id', $transaksi)
									->where('detail.jenis', 'Kambing/Domba')
									->whereNotNull('status.panziswil_status')
									->first();

				$item->jumlah_nama  = (is_null($temp->jumlah_nama)) ? 0 : count(explode(";", $temp->jumlah_nama));
				$item->jumlah       = (is_null($temp->jumlah)) ? 0 : $temp->jumlah;
				$item->sapi         = (is_null($sapi->jumlah)) ? 0 : $sapi->jumlah;
				$item->kambing      = (is_null($kambing->jumlah)) ? 0 : $kambing->jumlah;
				$item->persentase   = ($item->jumlah_nama != 0 && $item->target != 0) ? ($item->jumlah_nama / $item->target) * 100 : 0;
			}
		}

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }
}
