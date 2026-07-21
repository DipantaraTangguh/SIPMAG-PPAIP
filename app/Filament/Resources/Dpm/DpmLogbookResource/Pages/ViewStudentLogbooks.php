<?php

namespace App\Filament\Resources\Dpm\DpmLogbookResource\Pages;

use App\Filament\Resources\Dpm\DpmLogbookResource;
use App\Filament\Resources\Dpm\DpmLogbookResource\RelationManagers\LogbooksRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentLogbooks extends ViewRecord
{
    protected static string $resource = DpmLogbookResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Logbook - ' . $this->record->name . ' (' . $this->record->nim . ')';
    }

    public function getRelationManagers(): array
    {
        return [
            LogbooksRelationManager::class,
        ];
    }
}
