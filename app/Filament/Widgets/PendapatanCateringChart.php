<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\LaporanCatering;
use Carbon\Carbon;

class PendapatanCateringChart extends BarChartWidget
{
    protected static ?string $heading = 'Pendapatan Harian Catering (Bulan Ini)';

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = now()->format('Y-m');
    }

    protected function getFilters(): ?array
    {
        $start = Carbon::create(2025, 1, 1);
        $end = Carbon::create(2025, 12, 1);
        $months = [];
        for ($date = $start->copy(); $date->lte($end); $date->addMonth()) {
            $months[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }
        return $months;
    }

    protected function getData(): array
    {
        $selected = $this->filter ?? now()->format('Y-m');
        [$year, $month] = explode('-', $selected);

        $data = LaporanCatering::query()
            ->whereMonth('tanggal_pengantaran', $month)
            ->whereYear('tanggal_pengantaran', $year)
            ->selectRaw('DATE(tanggal_pengantaran) as tanggal, SUM(total_bayar) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $labels = $data->pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->translatedFormat('d F'))->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pendapatan Catering',
                    'data' => $values,
                    'backgroundColor' => '#f59e42',
                ],
            ],
        ];
    }
} 