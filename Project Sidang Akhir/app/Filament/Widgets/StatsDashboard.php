<?php

namespace App\Filament\Widgets;

use App\Models\Laporan;
use App\Models\Menu;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Widget;
use Filament\Widgets\BarChartWidget;

class StatsDashboard extends BaseWidget
{
    protected function getStats(): array
    {
        // Jumlah laporan bulan ini
        $jumlahLaporanBulanIni = Laporan::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Status laporan (misal: selesai, diproses, pending)
        $statusSelesai = Laporan::where('status', 'selesai')->count();
        $statusDiproses = Laporan::where('status', 'diproses')->count();
        $statusPending = Laporan::where('status', 'pending')->count();

        // Total pendapatan bulan ini
        $totalPendapatanBulanIni = Laporan::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Total pendapatan seluruh laporan
        $totalPendapatanSemuaLaporan = Laporan::sum('total_amount');
        // Total pendapatan seluruh laporan catering
        $totalPendapatanSemuaLaporanCatering = \App\Models\LaporanCatering::sum('total_bayar');
        // Total pendapatan gabungan
        $totalPendapatanGabungan = $totalPendapatanSemuaLaporan + $totalPendapatanSemuaLaporanCatering;

        return [
            // Card::make('Jumlah Laporan Bulan Ini', $jumlahLaporanBulanIni),
            Card::make('Pesanan Selesai', $statusSelesai),
            Card::make('Pesanan Diproses', $statusDiproses),
            Card::make('Pesanan Pending', $statusPending),
            Card::make('Total Pendapatan Makanan Harian', 'Rp ' . number_format($totalPendapatanSemuaLaporan, 0, ',', '.'))
                ->color($totalPendapatanSemuaLaporan >= 1000000 ? 'success' : 'danger'),
            Card::make('Total Pendapatan Catering', 'Rp ' . number_format($totalPendapatanSemuaLaporanCatering, 0, ',', '.'))
                ->color($totalPendapatanSemuaLaporanCatering >= 3000000 ? 'success' : 'danger'),
            Card::make('Total Pendapatan Gabungan', 'Rp ' . number_format($totalPendapatanGabungan, 0, ',', '.')),
        ];
    }
}
