<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seragamkan nama state jadi bahasa Inggris semua.
 *
 * Empat state sebelumnya memakai bahasa Indonesia sementara sisanya Inggris,
 * sehingga membingungkan saat dibaca. Pemetaannya:
 *   MenungguSidang     -> AwaitingDefense
 *   SiklusSelesai      -> CycleCompleted
 *   SelesaiNonWajib    -> ElectiveCompleted
 *   MenungguKonfirmasi -> AwaitingConfirmation
 *
 * Hanya untuk MySQL yang sudah terlanjur berisi data lama. Pada SQLite (test)
 * skema dibangun ulang dari migration create yang daftar nilainya sudah pakai
 * nama baru, jadi tidak ada yang perlu dikonversi.
 *
 * Urutannya penting: enum dilebarkan dulu supaya memuat nama lama DAN baru,
 * baru datanya di-update, lalu enum dipersempit ke nama baru saja.
 */
return new class extends Migration
{
    private const MAP = [
        'MenungguSidang' => 'AwaitingDefense',
        'SiklusSelesai' => 'CycleCompleted',
        'SelesaiNonWajib' => 'ElectiveCompleted',
        'MenungguKonfirmasi' => 'AwaitingConfirmation',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->convert(self::MAP);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->convert(array_flip(self::MAP));
    }

    /**
     * @param  array<string, string>  $map  nilai lama => nilai baru
     */
    private function convert(array $map): void
    {
        $old = array_keys($map);
        $new = array_values($map);

        // -- students.access_status ------------------------------------------
        $this->alterStudents(array_merge($this->studentBase(), $old, $new));

        foreach ($map as $from => $to) {
            DB::table('students')->where('access_status', $from)->update(['access_status' => $to]);
        }

        $this->alterStudents(array_merge($this->studentBase(), $new));

        // -- internship_cycles.outcome_status --------------------------------
        $cycleOld = array_values(array_intersect($old, ['SiklusSelesai', 'SelesaiNonWajib', 'CycleCompleted', 'ElectiveCompleted']));
        $cycleNew = array_values(array_intersect($new, ['SiklusSelesai', 'SelesaiNonWajib', 'CycleCompleted', 'ElectiveCompleted']));

        $this->alterCycles(array_merge($cycleOld, $cycleNew));

        foreach ($map as $from => $to) {
            if (! in_array($from, $cycleOld, true)) {
                continue;
            }
            DB::table('internship_cycles')->where('outcome_status', $from)->update(['outcome_status' => $to]);
        }

        $this->alterCycles($cycleNew);
    }

    /**
     * State yang namanya tidak berubah.
     *
     * @return array<int, string>
     */
    private function studentBase(): array
    {
        return [
            'Unverified',
            'PendingReview',
            'RejectedForm1',
            'ApprovedForm1',
            'HasApplication',
            'HasDPM',
            'LogbookComplete',
        ];
    }

    /**
     * @param  array<int, string>  $values
     */
    private function alterStudents(array $values): void
    {
        DB::statement(
            'ALTER TABLE students MODIFY access_status ENUM('
            .$this->quote($values)
            .") NOT NULL DEFAULT 'Unverified'"
        );
    }

    /**
     * @param  array<int, string>  $values
     */
    private function alterCycles(array $values): void
    {
        DB::statement(
            'ALTER TABLE internship_cycles MODIFY outcome_status ENUM('
            .$this->quote($values)
            .') NOT NULL'
        );
    }

    /**
     * @param  array<int, string>  $values
     */
    private function quote(array $values): string
    {
        return implode(',', array_map(
            static fn (string $v): string => "'".$v."'",
            array_values(array_unique($values)),
        ));
    }
};
