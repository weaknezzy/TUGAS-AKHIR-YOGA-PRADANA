<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditLaporan extends EditRecord
{
    protected static string $resource = LaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Method untuk membatasi akses
    public static function canEdit($record): bool
    {
        return Auth::user()->role === 'admin';
    }

    // Method untuk authorize access ke halaman
    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Anda tidak memiliki akses untuk mengedit laporan.');
    }
}
