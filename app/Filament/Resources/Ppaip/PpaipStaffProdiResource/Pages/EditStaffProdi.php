<?php

namespace App\Filament\Resources\Ppaip\PpaipStaffProdiResource\Pages;

use App\Filament\Resources\Ppaip\PpaipStaffProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffProdi extends EditRecord
{
    protected static string $resource = PpaipStaffProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
