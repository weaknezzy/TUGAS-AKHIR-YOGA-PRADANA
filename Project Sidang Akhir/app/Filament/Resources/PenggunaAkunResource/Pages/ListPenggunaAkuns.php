<?php

namespace App\Filament\Resources\PenggunaAkunResource\Pages;

use App\Filament\Resources\PenggunaAkunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenggunaAkuns extends ListRecords
{
    protected static string $resource = PenggunaAkunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
