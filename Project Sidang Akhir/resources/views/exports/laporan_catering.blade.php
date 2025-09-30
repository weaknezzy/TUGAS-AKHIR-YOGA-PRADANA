<!DOCTYPE html>
<html>
<head>
    <title>Laporan Catering</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 4px; word-break: break-all; }
        th { background: #eee; }
        th.menu, td.menu { width: 180px; overflow-wrap: break-word; white-space: pre-line; }
        th.catatan, td.catatan { width: 120px; overflow-wrap: break-word; white-space: pre-line; }
    </style>
</head>
<body>
    <h2>Laporan Catering</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pemesan</th>
                <th>No HP</th>
                <th>Alamat</th>
                <th>Acara</th>
                <th class="menu">Menu</th>
                <th>Tanggal Pemesanan</th>
                <th>Tanggal Pengantaran</th>
                <th>Jumlah Porsi</th>
                <th>Kemasan</th>
                <th>Metode Pembayaran</th>
                <th>Total Bayar</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->nama_pemesan }}</td>
                <td>{{ $row->no_hp }}</td>
                <td>{{ $row->alamat }}</td>
                <td>{{ $row->acara }}</td>
                <td class="menu">{{ $row->menu }}</td>
                <td>
                    {{ $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at)->format('Y-m-d') : '' }}
                </td>
                <td>
                    {{ $row->tanggal_pengantaran ? \Illuminate\Support\Carbon::parse($row->tanggal_pengantaran)->format('Y-m-d') : '' }}
                </td>
                <td>{{ $row->jumlah_porsi }}</td>
                <td>{{ $row->kemasan }}</td>
                <td>{{ $row->metode_pembayaran }}</td>
                <td>{{ $row->total_bayar }}</td>
                <td class="catatan">{{ $row->catatan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 