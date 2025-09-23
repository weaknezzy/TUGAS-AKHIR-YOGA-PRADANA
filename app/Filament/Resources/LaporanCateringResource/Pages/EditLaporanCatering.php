<?php

namespace App\Filament\Resources\LaporanCateringResource\Pages;

use App\Filament\Resources\LaporanCateringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditLaporanCatering extends EditRecord
{
    protected static string $resource = LaporanCateringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Tidak perlu lagi menghitung total dari items, langsung return data
        return $data;
    }

    // Method untuk membatasi akses
    public static function canEdit($record): bool
    {
        return Auth::user()->role === 'admin';
    }

    // Method untuk authorize access ke halaman
    protected function authorizeAccess(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Anda tidak memiliki akses untuk mengedit laporan catering.');
    }
} 