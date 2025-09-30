<?php

namespace App\Filament\Resources\LaporanCateringResource\Pages;

use App\Filament\Resources\LaporanCateringResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLaporanCatering extends CreateRecord
{
    protected static string $resource = LaporanCateringResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Tidak perlu lagi menghitung total dari items, langsung return data
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
        abort_unless(Auth::user()->role === 'admin', 403, 'Anda tidak memiliki akses untuk membuat laporan catering.');
    }
} 