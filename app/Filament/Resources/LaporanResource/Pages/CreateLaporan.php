<?php

namespace App\Filament\Resources\LaporanResource\Pages;

use App\Filament\Resources\LaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLaporan extends CreateRecord
{
    protected static string $resource = LaporanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set status berdasarkan kategori
        $data['status'] = $data['kategori'] === 'Catering' ? 'Diproses' : 'Pending';

        return $data;
    }

    // Method untuk membatasi akses
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'admin';
    }

    // Method untuk authorize access ke halaman
    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Anda tidak memiliki akses untuk membuat laporan.');
    }
}
