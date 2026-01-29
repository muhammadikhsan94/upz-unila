@extends('template.app')

@section('title')
- Laporan Realisasi Duta Zakat
@endsection

@section('content')

<div class="box box-info">

    <div class="box-header with-border">
        <h3 class="box-title"><strong>Laporan Rincian Kurban</strong></h3>
    </div>

    <form class="form-horizontal">
        <div class="box-body">
            <div class="col-md-6">
                <label for="id_lembaga" class="control-label">Wilayah:</label>
                <select id="wilayah" class="selectpicker col-sm-5" data-live-search="true" data-style="btn-success" data-size="5">
                    <option value="0">Tampil Semua</option>
                    @foreach($data['wilayah'] as $item)
                    <option value="{{ $item->id }}">{{ $item->nama_wilayah }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="box-body">
        <div class="dataTables_scrollBody">
            <div class="col-md-12">
                <table id="tabel-realisasi" class="display" style="width: 100%">
                    <thead>
                        <tr class="bg-primary">
                            <th> No </th>
                            <th> Daerah </th>
                            <th> Tgl Transaksi </th>
                            <th> No Punggung </th>
                            <th> Duta Zakat </th>
                            <th width="30%"> Pekurban </th>
                            <th> Realisasi Sapi/Kerbau </th>
                            <th> Realisasi Kambing/Domba </th>
                            <th> Jumlah Pekurban </th>
                            <th width="20%"> Lokasi </th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align:right">TOTAL:</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        var asal = <?php echo json_encode($data['user']) ?>;

        var table = $('#tabel-realisasi').DataTable({
            dom: 'Blfrtip',
            buttons: [
                {name: 'excelHtml5', extend: 'excelHtml5', text: 'Export to EXCEL', messageTop: 'Laporan Kurban Duta Zakat - Kabupaten/Kota '+asal.nama_wilayah, className: 'btn btn-default btn-sm', pageSize: 'A4', autoFilter: true, customize: function ( xlsx ){ var sheet = xlsx.xl.worksheets['sheet1.xml']; $('row c', sheet).attr( 's', '25' ); }, footer: true},
                {name: 'pdfHtml5', extend: 'pdfHtml5', text: 'Export to PDF', messageTop: 'Laporan Kurban Duta Zakat - Kabupaten/Kota '+asal.nama_wilayah, className: 'btn btn-default btn-sm', pageSize: 'A4', footer: true},
                {name: 'print', extend: 'print', text: 'PRINT', messageTop: 'Laporan Kurban Duta Zakat - Kabupaten/Kota '+asal.nama_wilayah, className: 'btn btn-default btn-sm', pageSize: 'A4', footer: true}
            ],
            "language": {
                "sEmptyTable": "DATA KOSONG ATAU TIDAK DITEMUKAN !",
                "sLengthMenu": "Tampilkan _MENU_ records",
                "sSearch": "Cari Data/Filter:",
            },
            "columnDefs": [
                {"className": "dt-center", "targets": [0, 6, 7, 8]},
            ],
            // "order": [[ 6, "DESC" ]],
            ajax: {
                url: "",
            },
            columns: [{
                    data: "id",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'daerah' },
                { data: 'tgl_transaksi' },
                { data: 'no_punggung' },
                { data: 'dutazakat' },
                { data: 'pekurban' },
                { data: 'sapi' },
                { data: 'kambing' },
                { data: 'jumlah_nama' },
                { data: 'lokasi' }
            ],
            footerCallback: function( tfoot, data, start, end, display ) {
                var api = this.api();
                $(api.column(6).footer()).html(
                    api.column(6).data().reduce(function ( a, b ) {
                        a = parseInt(a.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        b = parseInt(b.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        total = a+b;
                        return total;
                    }, 0)
                );
                $(api.column(7).footer()).html(
                    api.column(7).data().reduce(function ( a, b ) {
                        a = parseInt(a.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        b = parseInt(b.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        total = a+b;
                        return total;
                    }, 0)
                );
                $(api.column(8).footer()).html(
                    api.column(8).data().reduce(function ( a, b ) {
                        a = parseInt(a.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        b = parseInt(b.toString().replace(/,.*|[^0-9]/g, ''), 10);
                        total = a+b;
                        return total;
                    }, 0)
                );
            }
        });

        $('select').selectpicker();
        $('#wilayah').change(function() {
            table.ajax.url('/universitas/laporan/rincian_kurban/getdata/'+$(this).val()).load();
        });
        $('#wilayah').trigger("change");
    });
</script>
@endpush
@endsection
