<?php

namespace App\Filament\Resources\PenggunaAkunResource\Pages;

use App\Filament\Resources\PenggunaAkunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenggunaAkun extends EditRecord
{
    protected static string $resource = PenggunaAkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
