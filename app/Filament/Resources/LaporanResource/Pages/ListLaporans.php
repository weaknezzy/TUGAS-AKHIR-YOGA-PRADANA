<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\LaporanChartWidget;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use App\Exports\LaporanHarianExport;
use Illuminate\Support\Facades\Auth;

class ListLaporans extends ListRecords
{
    protected static string $resource = LaporanResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        
        // Hanya admin yang bisa create
        if (Auth::user()->role === 'admin') {
            $actions[] = Actions\CreateAction::make();
        }
        
        // Export actions untuk admin dan owner
        $actions[] = Action::make('export_excel')
            ->label('Export Excel')
            ->action(function () {
                $records = $this->getTableQuery()->get()->toArray();
                return Excel::download(new LaporanHarianExport($records), 'laporan_harian.xlsx');
            })
            ->requiresConfirmation()
            ->color('success');
            
        $actions[] = Action::make('export_pdf')
            ->label('Export PDF')
            ->action(function () {
                $records = $this->getTableQuery()->get();
                $pdf = Pdf::loadView('exports.laporan_harian', [
                    'records' => $records,
                ])->setPaper('a4', 'landscape');
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'laporan_harian.pdf'
                );
            })
            ->requiresConfirmation()
            ->color('danger');
            
        return $actions;
    }
}
