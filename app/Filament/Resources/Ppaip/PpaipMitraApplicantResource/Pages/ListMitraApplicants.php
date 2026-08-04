<?php

namespace App\Filament\Resources\Ppaip\PpaipMitraApplicantResource\Pages;

use App\Filament\Resources\Ppaip\PpaipMitraApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMitraApplicants extends ListRecords
{
    protected static string $resource = PpaipMitraApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportExcel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('mitra-applications.export')),
        ];
    }
}
