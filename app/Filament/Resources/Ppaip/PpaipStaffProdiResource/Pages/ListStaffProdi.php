<?php

namespace App\Filament\Resources\Ppaip\PpaipStaffProdiResource\Pages;

use App\Filament\Resources\Ppaip\PpaipStaffProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffProdi extends ListRecords
{
    protected static string $resource = PpaipStaffProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Tambah Staff Prodi')];
    }
}
