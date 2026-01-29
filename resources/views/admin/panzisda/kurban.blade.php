@extends('template.app')

@section('title')
- Data Transaksi
@endsection

@section('content')

<div class="box box-info">

    <div class="box-header with-border">
        <div class="col-md-6">
            <h3 class="box-title"><strong>Data Transaksi Kurban</strong></h3>
        </div>
    </div>

    <form class="form-horizontal">
        <div class="box-body">
            <div class="col-md-6">
                <label for="id_lembaga" class="control-label">STATUS:</label>
                <select id="status_transaksi" class="selectpicker col-sm-5" data-live-search="true" data-style="btn-success">
                    <option value="0">Tampil Semua</option>
                    <option value="1">Valid</option>
                    <option value="2">Tidak Valid</option>
                    <option value="3">Proses Panzisda</option>
                    <option value="4">Proses Manajer Group</option>
                </select>
            </div>
        </div>
    </form>

    <div class="box-body">

        <div class="dataTables_scrollBody">
            <div class="col-md-12">
                <table id="tabel-transaksi" class="display" style="width: 100%">
                    <thead>
                        <tr class="bg-primary">
                            <th> No </th>
                            <th> Tgl Transaksi </th>
                            <th> Nama Pekurban </th>
                            <th> Jenis Kurban </th>
                            <th> Jumlah </th>
                            <th> Status </th>
                            <th> <center>Aksi</center> </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Form Modal Start Edit Manajer -->
<div id="formDetail" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="exampleModalLongTitle">Detail Transaksi</h5>
            </div>
            <div class="modal-body">
                <span id="form-result"></span>
                <form class="form-horizontal" role="form" action="javascript:void(0)" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="id" id="id">

                    <div class="form-group ">
                        <label for="tgl_transaksi" class="col-sm-3 control-label">Tanggal Transaksi</label>
                        <div class="col-sm-9">
                            <input type="date" id="tgl_transaksi" class="form-control" disabled>
                        </div>
                    </div>

                    <div class="form-group ">
                        <label for="jenis" class="col-sm-3 control-label">Jenis Kurban</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="jenis" name="jenis" disabled>
                        </div>
                    </div>

                    <div id="jenis_kurban_1">
                        <div class="form-group">
                            <label for="jumlah" class="col-sm-3 control-label">Jumlah Pekurban<br><sup><i>(Kambing/Domba)</i></sup></label>
                            <div class="col-sm-2">
                                <input type="number" id="jumlah_1" name="jumlah_1" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nama" class="col-sm-3 control-label">Nama Pekurban<br><sup><i>(Kambing/Domba)</i></sup></label>
                            <div class="col-sm-9">
                                <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="nama_1" name="nama_1" disabled></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="jenis_kurban_2">
                        <div class="form-group">
                            <label for="jumlah" class="col-sm-3 control-label">Jumlah Pekurban<br><sup><i>(Sapi/Kerbau)</i></sup></label>
                            <div class="col-sm-2">
                                <input type="number" id="jumlah_2" name="jumlah_2" class="form-control" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nama" class="col-sm-3 control-label">Nama Pekurban<br><sup><i>(Sapi/Kerbau)</i></sup></label>
                            <div class="col-sm-9">
                                <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="nama_2" name="nama_2" disabled></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lokasi" class="col-sm-3 control-label">Lokasi</label>
                        <div class="col-sm-9">
                            <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="lokasi" name="lokasi" disabled></textarea>
                        </div>
                    </div>

                    <div class="form-group" id="show_komentar">
                        <label for="lokasi" class="col-sm-3 control-label">Komentar</label>
                        <div class="col-sm-9">
                            <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="status_komentar" disabled></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger mr-5" id="btnNonValid" onclick="NonValidFunction()">Tidak Valid</button>
                        <button type="button" class="btn btn-primary mr-5" id="btnValid" onclick="ValidFunction()">Valid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Form Modal End Edit Manajer -->

<!-- Validasi Transaksi -->
<div id="modalValid" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="exampleModalLongTitle">KONFIRMASI</h5>
            </div>
            <div class="modal-body">
                <span id="form-result"></span>
                <form class="form-horizontal" role="form" id="formValid" method="post" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="idTrx1" name="idTrx1">
                    <input type="hidden" name="setujui" id="setujui" value="OK">

                    Yakin ingin memvalidasi transaksi ini ?

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Validasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Validasi Transaksi End -->

<!-- Validasi Transaksi -->
<div id="modalNonValid" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="exampleModalLongTitle">KONFIRMASI</h5>
            </div>
            <div class="modal-body">
                <span id="form-result"></span>
                <form class="form-horizontal" role="form" id="formNonValid" method="post" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="idTrx2" name="idTrx2">
                    <input type="hidden" name="setujui" id="setujui" value="BATAL">

                    <div class="form-group">
                        <label for="komentar" class="col-md-3 control-label">Komentar</label>
                        <div class="col-md-9">
                            <textarea style="resize: none;" class="form-control" id="komentar" name="komentar" placeholder="Masukkan komentar"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Validasi Transaksi End -->

@push('scripts')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
<script>
    var id_trx;

    function ValidFunction()
    {
        $('#btnNonValid').removeAttr("data-target", "#modalNonValid");
        $('#btnNonValid').removeAttr("data-toggle", "modal");
        $('#btnValid').attr("data-target", "#modalValid");
        $('#btnValid').attr("data-toggle", "modal");
        $('#formDetail').modal('hide');
        $('#idTrx1').val(id_trx);
    }

    function NonValidFunction()
    {
        $('#btnNonValid').attr("data-target", "#modalNonValid");
        $('#btnNonValid').attr("data-toggle", "modal");
        $('#btnValid').removeAttr("data-target", "#modalValid");
        $('#btnValid').removeAttr("data-toggle", "modal");
        $('#formDetail').modal('hide');
        $('#idTrx2').val(id_trx);
    }

    $(document).ready(function() {
        var table = $('#tabel-transaksi').DataTable({
            dom: 'lfrtip',
            "language": {
                "sEmptyTable": "DATA KOSONG ATAU TIDAK DITEMUKAN !",
                "sLengthMenu": "Tampilkan _MENU_ records",
                "sSearch": "Cari Data/Filter:",
            },
            ajax: {
                url: "",
            },
            "columnDefs": [
                {"className": "dt-center", "targets": [0, 4, 5, 6]}
            ],
            columns: [
                {
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'tgl_transaksi' },
                { data: 'nama' },
                { data: 'jenis' },
                { data: 'jumlah' },
                { data: 'panzisda_status' },
                { data: 'aksi', name: 'aksi' }
            ]
        });

        $('select').selectpicker();
        $('#status_transaksi').change(function() {
            table.ajax.url('/fakultas_lembaga/transaksi_kurban/getdata/'+$(this).val()).load();
        });
        $('#status_transaksi').trigger("change");

        $(document).on('click', '.detail', function() {
            var id = $(this).attr('id');

            $.ajax({
                method: "GET",
                url: "transaksi_kurban/detail/" + id,
                dataType: "json",
                success: function(data) {
                    id_trx = id;
                    $('#id').val(id);
                    $('#jenis').val(data.jenis);
                    $('#tgl_transaksi').val(data.tgl_transaksi);
                    $('#lokasi').val(data.lokasi);
                    $('#jenis_kurban_1').hide();
                    $('#jenis_kurban_2').hide();

                    for(let [i, datas] of Object.entries(data.jenis)) {
                        let getNama = data.nama[i].split(";");
                        var nama;
                        let t = 0;
                        if(datas == "Kambing/Domba") {
                            $('#jenis_kurban_1').show();
                            $('#jumlah_1').val(data.jumlah[i]);
                            for(x in getNama) {
                                t += 1;
                                if(x == 0) {
                                    nama = t + '. ' + getNama[x];
                                } else {
                                    nama = nama + '\n' + t + '. ' + getNama[x];
                                }
                            }
                            $('#nama_1').val(nama);
                        } else {
                            $('#jenis_kurban_2').show();
                            $('#jumlah_2').val(data.jumlah[i]);
                            for(x in getNama) {
                                t += 1;
                                if(x == 0) {
                                    nama = t + '. ' + getNama[x];
                                } else {
                                    nama = nama + '\n' + t + '. ' + getNama[x];
                                }
                            }
                            $('#nama_2').val(nama);
                        }
                    }

                    if (data.komentar == null) {
                        $('#show_komentar').hide();
                    } else {
                        $('#show_komentar').show();
                        $('#status_komentar').val(data.komentar);
                    }

                    if (data.panzisda_status == null && data.komentar == null) {
                        $('#btnValid').show();
                        $('#btnNonValid').show();
                    } else {
                        $('#btnValid').hide();
                        $('#btnNonValid').hide();
                    }
                    $('#formDetail').modal('show');
                },
                error: function() {
                    alert('Error : Cannot get data!');
                }
            });
        });

        //Submit
        $('#formValid').submit(function (e) {
            e.preventDefault();
            let formDataValid = new FormData(this);

            $.ajax({
                type: "POST",
                url: "{{route('panzisda.updateStatusKurban')}}",
                data: formDataValid,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#formValid')[0].reset();
                    $('#tabel-transaksi').DataTable().ajax.reload();
                    $('#modalValid').modal('hide');
                },
                error: function (data) {
                    var html = '';
                    alert("Data gagal disimpan, silahkan di cek kembali dan jangan ada data kosong!")
                    html = '<div class="alert alert-danger">' + data + '</div>';
                }
            })
        });

        $('#formNonValid').submit(function (e) {
            e.preventDefault();
            let formDataNonValid = new FormData(this);

            $.ajax({
                type: "POST",
                url: "{{route('panzisda.updateStatusKurban')}}",
                data: formDataNonValid,
                contentType: false,
                processData: false,
                success: function (data) {
                    $('#formNonValid')[0].reset();
                    $('#tabel-transaksi').DataTable().ajax.reload();
                    $('#modalNonValid').modal('hide');
                },
                error: function (data) {
                    var html = '';
                    alert("Data gagal disimpan, silahkan di cek kembali dan jangan ada data kosong!")
                    html = '<div class="alert alert-danger">' + data + '</div>';
                }
            })
        });
    });
</script>
@endpush
@endsection
