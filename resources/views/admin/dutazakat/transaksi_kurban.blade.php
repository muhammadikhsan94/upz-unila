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

    <div class="box-body">
        <div class="col-md-6" style="margin-bottom: 20px;">
            <a class="btn btn-primary btn-md" href="{{url('/duta/transaksi_kurban/tambah')}}">
                <i class="fa fa-plus"> Tambah Transaksi </i>
            </a>
        </div>

        <div class="dataTables_scrollBody">
            <div class="col-md-12">
                <table id="tabel-transaksi" class="display" style="width: 100%">
                    <thead>
                        <tr class="bg-success">
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
                            <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="komentar" name="komentar" disabled></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Form Modal End Edit Manajer -->

<!-- DELETE TRANSAKSI -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                KONFIRMASI
            </div>
            <div class="modal-body">
                Yakin ingin menghapus?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <a name="ok-button" id="ok-button" class="btn btn-danger btn-ok">Hapus</a>
            </div>
        </div>
    </div>
</div>
<!--END DELETE TRANSAKSI -->

@push('scripts')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#tabel-transaksi').DataTable({
            dom: 'lfrtip',
            "language": {
                "sEmptyTable": "DATA KOSONG ATAU TIDAK DITEMUKAN !",
                "sLengthMenu": "Tampilkan _MENU_ records",
                "sSearch": "Cari Data/Filter:",
            },
            ajax: {
                url: "{{ url('/duta/transaksi_kurban/getdata') }}",
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
                { data: 'manajer_status' },
                { data: 'aksi', name: 'aksi' }
            ]
        });

        $(document).on('click', '.detail', function() {
            var id = $(this).attr('id');

            $.ajax({
                method: "GET",
                url: "/duta/transaksi_kurban/detail/" + id,
                dataType: "json",
                success: function(data) {
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
                        $('#komentar').val(data.komentar);
                    }
                    $('#formDetail').modal('show');
                },
                error: function() {
                    alert('Error : Cannot get data!');
                }
            });
        });

        $(document).on('click', '.edit', function() {
            var id = $(this).attr('id');
            window.location = "/duta/transaksi_kurban/edit/"+id;
        });

        $(document).on('click', '.delete', function() {
            $('#confirmModal').modal('show');
            trxId = $(this).attr('id');
        });

        $('#ok-button').click(function() {
            $.ajax({
                url: "/duta/transaksi_kurban/delete/" + trxId,
                method: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                beforeSend: function() {
                    $('#ok-button').text('Menghapus...');
                },
                success: function(data) {
                    setTimeout(function() {
                        if (data.errors) {
                            errorMessage = '';
                            for (var count = 0; count < data.errors.length; count++) {
                                errorMessage += data.errors[count];
                            }
                            $('#confirmModal').modal('hide');
                            $('#tabel-transaksi').DataTable().ajax.reload();
                            alert(errorMessage);
                        } else {
                            $('#confirmModal').modal('hide');
                            $('#tabel-transaksi').DataTable().ajax.reload();
                            alert('Data Deleted');
                            $('#ok-button').text('Hapus');
                        }
                    }, 2000);
                }
            })
        });
    });
</script>
@endpush
@endsection