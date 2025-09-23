<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanCateringExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
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
                'Nama Pemesan' => $row['nama_pemesan'] ?? '',
                'No HP' => $row['no_hp'] ?? '',
                'Alamat' => $row['alamat'] ?? '',
                'Acara' => $row['acara'] ?? '',
                'Menu' => $row['menu'] ?? '',
                'Tanggal Pemesanan' => isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '',
                'Tanggal Pengantaran' => isset($row['tanggal_pengantaran']) ? date('Y-m-d', strtotime($row['tanggal_pengantaran'])) : '',
                'Jumlah Porsi' => $row['jumlah_porsi'] ?? '',
                'Kemasan' => $row['kemasan'] ?? '',
                'Metode Pembayaran' => $row['metode_pembayaran'] ?? '',
                'Total Bayar' => $row['total_bayar'] ?? '',
                'Catatan' => $row['catatan'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Pemesan',
            'No HP',
            'Alamat',
            'Acara',
            'Menu',
            'Tanggal Pemesanan',
            'Tanggal Pengantaran',
            'Jumlah Porsi',
            'Kemasan',
            'Metode Pembayaran',
            'Total Bayar',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = count($this->records) + 1;
        $range = 'A1:M' . $rowCount;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        return [];
    }
} 