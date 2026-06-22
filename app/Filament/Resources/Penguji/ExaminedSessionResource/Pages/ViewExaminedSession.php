<?php

namespace App\Filament\Resources\Penguji\ExaminedSessionResource\Pages;

use App\Filament\Resources\Penguji\ExaminedSessionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewExaminedSession extends ViewRecord
{
    protected static string $resource = ExaminedSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
