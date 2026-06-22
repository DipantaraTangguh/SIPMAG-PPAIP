<?php

namespace App\Filament\Resources\Penguji\ExaminedSessionResource\Pages;

use App\Filament\Resources\Penguji\ExaminedSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListExaminedSessions extends ListRecords
{
    protected static string $resource = ExaminedSessionResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(ExaminedSessionResource::canAccess(), 403);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
