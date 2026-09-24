<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping user Kaprodi SI ke record lecturer.
        // Mapping user Kaprodi IF ke record lecturer.
        // DPM demo pertama.
        // DPM demo kedua.
        // DPM demo ketiga.
        // Dosen penguji demo. user_id 8 -- bergeser dari 9 setelah user
        // "Raka Logbook Pratama" (dulu id 8) dihapus dari UserSeeder.

        DB::table('lecturers')->insert([
            [
                'user_id' => 2,
                'nidn' => '0422117502',
                'lecturer_name' => 'Kaprodi SIF',
                'contact' => 'kaprodi-sif@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'nidn' => '0315098801',
                'lecturer_name' => 'Kaprodi TIF',
                'contact' => 'kaprodi-tif@bakrie.ac.id',
                'study_program' => 'Informatika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'nidn' => '0412058801',
                'lecturer_name' => 'Dospem SIF 1',
                'contact' => 'dospem-sif-1@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'nidn' => '0508077601',
                'lecturer_name' => 'Dospem SIF 2',
                'contact' => 'dospem-sif-2@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'nidn' => '0623098502',
                'lecturer_name' => 'Dospem TIF 2',
                'contact' => 'dospem-tif-2@bakrie.ac.id',
                'study_program' => 'Informatika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8,
                'nidn' => '0316048703',
                'lecturer_name' => 'Dospeng TID 2',
                'contact' => 'dospeng-tid-2@bakrie.ac.id',
                'study_program' => 'Teknik Industri',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
