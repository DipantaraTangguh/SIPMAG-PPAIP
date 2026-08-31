<?php

namespace App\Http\Requests\Logbooks;

use App\Models\Logbook;
use App\Models\Student;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreLogbookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->user()->student;

        return [
            'tanggal' => $this->dateRules($student),
            'kegiatan_harian' => 'required|string',
            'hasil' => 'required|string',
        ];
    }

    protected function dateRules(Student $student): array
    {
        $period = $this->internshipPeriod($student);

        if (! $period) {
            throw ValidationException::withMessages([
                'tanggal' => 'Periode magang belum tersedia. Lengkapi pengajuan pembimbing terlebih dahulu.',
            ]);
        }

        return [
            'required',
            'date',
            'after_or_equal:'.$period['start_date'],
            'before_or_equal:'.$period['maximum_date'],
            function (string $attribute, mixed $value, Closure $fail) use ($student): void {
                // Entri yang ditolak tidak menghalangi tanggalnya dipakai
                // lagi: mahasiswa memperbaiki dengan mengirim entri baru,
                // bukan menyunting yang lama. Yang dijaga tinggal satu --
                // tidak boleh ada dua entri hidup untuk tanggal yang sama.
                $duplicateExists = Logbook::query()
                    ->where('student_id', $student->id)
                    ->whereDate('tanggal', $value)
                    ->where('status', '!=', 'Rejected')
                    ->exists();

                if ($duplicateExists) {
                    $fail('Logbook untuk tanggal ini sudah ada.');
                }
            },
        ];
    }

    protected function internshipPeriod(Student $student): ?array
    {
        $application = $student->supervisorApplication;

        if (! $application?->mulai_magang || ! $application->selesai_magang) {
            return null;
        }

        return [
            'start_date' => $application->mulai_magang->toDateString(),
            'end_date' => $application->selesai_magang->toDateString(),
            'maximum_date' => $application->selesai_magang->min(today())->toDateString(),
        ];
    }
}
