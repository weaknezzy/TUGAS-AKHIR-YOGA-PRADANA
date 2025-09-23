<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanHarianExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return collect($this->records)->map(function ($row, $i) {
            return [
                'No' => $i + 1,
                'Nama Customer' => $row['customer_name'] ?? '',
                'No Telp' => $row['no_telp'] ?? '',
                'Tanggal Pemesanan' => isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '',
                'Item Pesanan' => $row['order_items'] ?? '',
                'Total' => $row['total_amount'] ?? '',
                'Metode Pembayaran' => $row['payment_method'] ?? '',
                'Status' => $row['status'] ?? '',
                'Catatan' => $row['note'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Customer',
            'No Telp',
            'Tanggal Pemesanan',
            'Item Pesanan',
            'Total',
            'Metode Pembayaran',
            'Status',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->records) + 1;
        $range = 'A1:I' . $rowCount;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        return [];
    }
} 