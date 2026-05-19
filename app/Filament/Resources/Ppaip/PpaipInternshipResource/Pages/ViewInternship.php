<?php

namespace App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages;

use App\Filament\Resources\Ppaip\PpaipInternshipResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInternship extends ViewRecord
{
    protected static string $resource = PpaipInternshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
