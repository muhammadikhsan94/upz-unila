@extends('template.app')

@section('content')
<div class="row">
	<div class="col-md-9">
		<div class="alert alert-warning alert-dismissible">
			<div class="inner">
				<h4><i class="fa fa-exclamation-circle red"></i> Selamat Datang, <b>{{ strtoupper(Auth::user()->nama) }}</b>.</h4>
				<p style="text-align: justify;">Berikut ini adalah halaman aplikasi zakat untuk anda sebagai Relawan. Jika anda bukan sebagai Relawan, silahkan hubungi Fakultas/Lembaga/Biro anda. Terima Kasih.</p>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="small-box bg-red">
			<div class="inner">
				<h3>{{ $data['transaksi'] }}</h3>
				<p>Total Transaksi Kurban</p>
			</div>
			<div class="icon">
				<i class="fa fa-shopping-cart"></i>
			</div>
		</div>
	</div>
</div>

<div class="row">

	<div class="col-lg-6">
		<div class="small-box bg-green">
			<div class="inner">
			<h3>{{number_format($data['realisasi'],2)}}<sup style="font-size: 20px">%</sup></h3>
				<p>Persentase Realisasi</p>
			</div>
			<div class="icon">
				<i class="fa fa-percent"></i>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
        <div class="small-box bg-primary">
			<div class="inner">
				<h3>{{ $data['pekurban'] }}</h3>
				<p>Total Pekurban</p>
			</div>
			<div class="icon">
                <i class="fa fa-users"></i>
			</div>
		</div>
    </div>

</div>

<div class="row">

	<div class="col-lg-6">
		<div class="small-box bg-teal">
			<div class="inner">
				<h3>{{ $data['kambing'] }}</h3>
				<p>Total Pengumpulan Kambing/Domba</p>
			</div>
			<div class="icon">
                <i class="fa fa-shopping-cart"></i>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
        <div class="small-box bg-aqua">
			<div class="inner">
				<h3>{{ $data['sapi'] }}</h3>
				<p>Total Pengumpulan Sapi/Kerbau</p>
			</div>
			<div class="icon">
                <i class="fa fa-shopping-cart"></i>
			</div>
		</div>
    </div>

</div>
@endsection
