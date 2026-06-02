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

        DB::table('lecturers')->insert([
            [
                'user_id'       => 2,
                'nidn'          => '0422117502',
                'lecturer_name' => 'Prof. Dr. Hoga Saragih, ST, MT',
                'contact'       => 'hoga.saragih@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 3,
                'nidn'          => '0315098801',
                'lecturer_name' => 'Dr. Budi Santoso, M.T.',
                'contact'       => 'budi.santoso@bakrie.ac.id',
                'study_program' => 'Informatika',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 4,
                'nidn'          => '0412058801',
                'lecturer_name' => 'Dita Nurmadewi, S.Kom.',
                'contact'       => 'dita.nurmadewi@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 5,
                'nidn'          => '0508077601',
                'lecturer_name' => 'Dr. Ahmad Fauzi, M.T.',
                'contact'       => 'ahmad.fauzi@bakrie.ac.id',
                'study_program' => 'Sistem Informasi',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'user_id'       => 6,
                'nidn'          => '0623098502',
                'lecturer_name' => 'Siti Aminah, M.Kom.',
                'contact'       => 'siti.aminah@bakrie.ac.id',
                'study_program' => 'Informatika',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
