<?php

namespace App\Filament\Resources\LaporanCateringResource\Pages;

use App\Filament\Resources\LaporanCateringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanCateringExport;
use Illuminate\Support\Facades\Auth;

class ListLaporanCaterings extends ListRecords
{
    protected static string $resource = LaporanCateringResource::class;

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
                return Excel::download(new LaporanCateringExport($records), 'laporan_catering.xlsx');
            })
            ->requiresConfirmation()
            ->color('success');
            
        $actions[] = Action::make('export_pdf')
            ->label('Export PDF')
            ->action(function () {
                $records = $this->getTableQuery()->get();
                $pdf = Pdf::loadView('exports.laporan_catering', [
                    'records' => $records,
                ])->setPaper('a4', 'landscape');
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'laporan_catering.pdf'
                );
            })
            ->requiresConfirmation()
            ->color('danger');
            
        return $actions;
    }
} 