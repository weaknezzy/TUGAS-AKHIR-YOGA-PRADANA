<!DOCTYPE html>
<html>
<head>
    <title>Laporan Harian</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 4px; word-break: break-all; }
        th { background: #eee; }
        th.items, td.items { width: 180px; overflow-wrap: break-word; white-space: pre-line; }
        th.catatan, td.catatan { width: 120px; overflow-wrap: break-word; white-space: pre-line; }
    </style>
</head>
<body>
    <h2>Laporan Harian</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Customer</th>
                <th>No Telp</th>
                <th>Tanggal Pemesanan</th>
                <th class="items">Item Pesanan</th>
                <th>Total</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th class="catatan">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->customer_name }}</td>
                <td>{{ $row->no_telp }}</td>
                <td>
                    {{ $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at)->format('Y-m-d') : '' }}
                </td>
                <td class="items">{{ $row->order_items }}</td>
                <td>{{ $row->total_amount }}</td>
                <td>{{ $row->payment_method }}</td>
                <td>{{ $row->status }}</td>
                <td class="catatan">{{ $row->note }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 