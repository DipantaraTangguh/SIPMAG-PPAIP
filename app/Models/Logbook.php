<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logbook extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'student_id',
        'tanggal',
        'kegiatan_harian',
        'hasil',
        'status',
        'dpm_note',
    ];

    /**
     * Nilai bawaan status juga ditulis di sini, bukan cuma sebagai default
     * kolom di database. Mahasiswa tidak mengirim status saat membuat
     * logbook, jadi tanpa ini instance hasil Logbook::create() pulang dengan
     * status null -- default kolomnya baru terisi di sisi database dan tidak
     * ikut kebaca kembali. Akibatnya respons API menyertakan status null dan
     * badge di tabel logbook mahasiswa tampil kosong sampai halaman dimuat
     * ulang.
     */
    protected $attributes = [
        'status' => 'PendingReview',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
