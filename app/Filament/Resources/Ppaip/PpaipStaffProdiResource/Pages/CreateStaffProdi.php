<?php

namespace App\Filament\Resources\Ppaip\PpaipStaffProdiResource\Pages;

use App\Filament\Resources\Ppaip\PpaipStaffProdiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffProdi extends CreateRecord
{
    protected static string $resource = PpaipStaffProdiResource::class;

    /**
     * Role tidak ditampilkan di formulir karena tidak ada pilihan lain:
     * staff bertindak sebagai Kaprodi, dan yang membedakannya dari Kaprodi
     * sungguhan adalah ketiadaan baris dosen -- bukan role tersendiri.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'kaprodi';

        return $data;
    }
}
