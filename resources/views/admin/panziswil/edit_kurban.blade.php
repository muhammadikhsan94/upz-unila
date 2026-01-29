@extends('template.app')

@section('title')
- Edit Transaksi
@endsection

@section('content')

<form id="formTambah" class="form-horizontal" method="post" action="javascript:void(0)" enctype="multipart/form-data">
    @csrf
    <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Edit Data Transaksi Kurban</h3></div>
        <div class="box-body">
            <input type="hidden" name="id" id="id">
            <div class="form-group">
                <label for="jenis" class="col-sm-3 control-label">Jenis Kurban</label>
                <div class="col-sm-5">
                    <select data-size="2" id="jenis" name="jenis[]" class="selectpicker" data-live-search="true" title="Pilih Jenis Kurban.." oninvalid="this.setCustomValidity('data tidak boleh kosong!')" onchange="setCustomValidity('')" required multiple>
                        <option value="Kambing/Domba">Kambing/Domba</option>
                        <option value="Sapi/Kerbau">Sapi/Kerbau</option>
                    </select>
                </div>
            </div>
            <div id="jenis_kurban_1">
                <div class="form-group">
                    <label for="jumlah" class="col-sm-3 control-label">Jumlah Pekurban<br><sup><i>(Kambing/Domba)</i></sup></label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="number" id="jumlah_1" name="jumlah_1" min="1" max="50" class="form-control" placeholder="Masukkan Jumlah" oninvalid="this.setCustomValidity('Jumlah harus minimal 0 dan maksimal 50!')" onchange="setCustomValidity('')" >
                            <i>Maksimal 50</i>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="nama" class="col-sm-3 control-label">Nama Pekurban<br><sup><i>(Kambing/Domba)</i></sup></label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="nama_1" name="nama_1" placeholder="Contoh:&#10;Agus Raharjo&#10;Bakti Raharjo"></textarea>
                            <i>Pisahkan nama dengan menekan <<strong>enter</strong>></i>
                        </div>
                    </div>
                </div>
            </div>
            <div id="jenis_kurban_2">
                <div class="form-group">
                    <label for="jumlah" class="col-sm-3 control-label">Jumlah Pekurban<br><sup><i>(Sapi/Kerbau)</i></sup></label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <input name="jumlah_2" value="1" type="hidden">
                            <select data-size="7" id="jumlah_select" class="selectpicker" data-live-search="true" title="Pilih Jumlah Pekurban.." oninvalid="this.setCustomValidity('data tidak boleh kosong!')" onchange="setCustomValidity('')">
                                <?php $x = 7 ?>
                                @for($x;$x>=1;$x--)
                                <option value="{{ $x }}">{{ '1 Sapi/Kerbau Untuk '.$x.' Orang' }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="nama" class="col-sm-3 control-label">Nama Pekurban<br><sup><i>(Sapi/Kerbau)</i></sup></label>
                    <div class="col-sm-6">
                        <div class="input-group">
                            <textarea style="resize: none;" rows="5" cols="100%" class="form-control" id="nama_2" name="nama_2" placeholder="Contoh:&#10;Agus Raharjo&#10;Bakti Raharjo"></textarea>
                            <i>Pisahkan nama dengan menekan <<strong>enter</strong>></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="lokasi" class="col-sm-3 control-label">Lokasi Penyaluran</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <textarea style="resize: none;" rows="3" cols="100%" class="form-control" id="lokasi" name="lokasi" placeholder="Masukkan Lokasi Penyaluran" required></textarea>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <input type="submit" name="btn" value="Simpan" id="submitBtn" class="btn btn-primary" onclick="MyFunction(event)" />
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                KONFIRMASI
            </div>
            <div class="modal-body">
                Yakin ingin menyimpan? jika belum yakin, silahkan di cek kembali datanya..
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Kembali</button>
                <a id="ok-button" name="ok-button" class="btn btn-success btn-ok">Kirim</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    function MyFunction(e)
    {
        if($('#jenis').val() == '') {
            alert('Data tidak boleh kosong!');
        } else {

            var errors = 0;

            var nama1 = $('#nama_1').val();
            if(nama1 != '') {
                var splitNama1 = nama1.split(/\r?\n/g);

                if(splitNama1.length != $('#jumlah_1').val()) {
                    errors=1;
                    alert('Jumlah pekurban (kambing/domba) dengan jumlah nama pekurban tidak sama!');
                    $('#nama_1').focus();
                }

                var t = 1;
                for(var x=0;x<splitNama1.length;x++) {
                    if(splitNama1[x] == '') {
                        errors=1;
                        alert('nama pekurban (kambing/domba) nomor '+t+' tidak boleh kosong');
                        $('#nama_1').focus();
                    }
                    t = t+1;
                }
            }

            var nama2 = $('#nama_2').val();
            if(nama2 != '') {
                var splitNama2 = nama2.split(/\r?\n/g);

                if(splitNama2.length != $('#jumlah_select').val()) {
                    errors=1;
                    alert('Jumlah pekurban (sapi/kerbau) dengan jumlah nama pekurban tidak sama!');
                    $('#nama_2').focus();
                }

                var t = 1;
                for(var x=0;x<splitNama2.length;x++) {
                    if(splitNama2[x] == '') {
                        errors=1;
                        alert('nama pekurban (sapi/kerbau) nomor '+t+' tidak boleh kosong');
                        $('#nama_2').focus();
                    }
                    t = t+1;
                }
            }

            //Validasi Required
            for (const el of document.getElementById('formTambah').querySelectorAll("[required]")) {
                console.log(el, el.reportValidity());
                if (!el.reportValidity()) {
                    errors=1;
                    alert('data tidak boleh kosong');
                }
            }

            if(errors!=1 && $('#lokasi').val() != '') {
                $('#submitBtn').attr("data-target", "#confirmModal");
                $('#submitBtn').attr("data-toggle", "modal");
            }
        }
    }

    $(document).ready(function() {

        $('#nama_1').on('keypress',function(e) {
            if(e.which == 13) {
                var jumlah = $('#jumlah_1').val();
                var nama = $('#nama_1').val();
                var split = nama.split(/\r?\n/g);
                if(split.length == jumlah) {
                    alert('nama pekurban harus berjumlah '+jumlah);
                    event.preventDefault();
                }
            }
        });

        $('#nama_2').on('keypress',function(e) {
            if(e.which == 13) {
                var jumlah = $('#jumlah_select').val();
                var nama = $('#nama_2').val();
                var split = nama.split(/\r?\n/g);
                if(split.length == jumlah) {
                    alert('nama pekurban harus berjumlah '+jumlah);
                    event.preventDefault();
                }
            }
        });

        //Define
        var data = <?php echo json_encode($data); ?>;
        var jenis = [];
        for(x in data.detail) {
            jenis.push(data.detail[x].jenis);
        }

        //View Data
        $('#id').val(data.transaksi.id);
        $('select[id=jenis').selectpicker('val', jenis);
        $('#lokasi').val(data.transaksi.lokasi);

        $('#jenis').change(function() {
            var choice = $('#jenis').val();
            console.log(choice);
            $('#jenis_kurban_1').hide();
            $('#jenis_kurban_2').hide();
            $('#jumlah_1').removeAttr('required','');
            $('#jumlah_2').removeAttr('required','');
            $('#jumlah_1').val(null);
            $('#jumlah_select').removeAttr('required','');
            $('#nama_1').removeAttr('required','');
            $('#nama_2').removeAttr('required','');
            $('#nama_1').val(null);
            $('#nama_2').val(null);

            for (let [i, datas] of Object.entries(choice)) {
                var rows = (data.detail[i] != null) ? data.detail[i].nama.split(";") : '';
                var nama = (rows != '') ? rows.join('\n') : '';

                if(datas == 'Kambing/Domba') {
                    $('#jenis_kurban_1').show();
                    $('#jumlah_1').attr('required','');
                    $('#jumlah_1').val(data.detail[i].jumlah);
                    $('#nama_1').attr('required','');
                    $('#nama_1').val(nama);
                } else {
                    $('#jenis_kurban_2').show();
                    $('#jumlah_2').attr('required','');
                    $('#jumlah_select').selectpicker('val', rows.length);
                    $('#nama_2').attr('required','');
                    $('#nama_2').val(nama);
                }
            };
        });
        $("#jenis").trigger("change");

        //Submit
        $('#ok-button').on('click', function(e) {
            e.preventDefault();
            let formData = new FormData(document.getElementById("formTambah"));
            $.ajax({
                type: "POST",
                url: "{{route('panziswil.updateKurban')}}",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#ok-button').text('Kirim...');
                },
                success: function (data) {
                    $('#formTambah')[0].reset();
                    var html = '';
                    alert("Data berhasil disimpan!")
                    html = '<div class="alert alert-default">' + data + '</div>';
                    window.location.replace("{{url('transaksi_kurban')}}");
                },
                error: function (data) {
                    $('#ok-button').text('Kirim');
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
