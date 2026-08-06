<?php

namespace App\Filament\Resources\Shared\InternshipCycleResource\Pages;

use App\Filament\Resources\Shared\InternshipCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternshipCycles extends ListRecords
{
    protected static string $resource = InternshipCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportExcel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('rekap-magang.export')),
        ];
    }
}
