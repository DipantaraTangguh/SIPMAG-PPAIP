<?php

namespace App\Services;

use App\Models\DefenseAssessment;
use App\Models\DefenseSubmission;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DefenseAssessmentService
{
    public const INTERNSHIP_PERFORMANCE_WEIGHT = 0.50;

    public const FINAL_REPORT_WEIGHT = 0.30;

    public const PRESENTATION_WEIGHT = 0.20;

    /**
     * @param  array{
     *     internship_performance_score: float|int|string,
     *     final_report_score: float|int|string,
     *     presentation_score: float|int|string
     * }  $scores
     */
    public function save(User $user, DefenseSubmission $submission, array $scores): DefenseAssessment
    {
        if (! $user->can('assess', $submission)) {
            throw new AuthorizationException;
        }

        $lecturerId = $user->lecturer?->id;
        $assessorRole = $this->assessorRole($user, $submission);

        if (! $lecturerId || ! $assessorRole) {
            throw new AuthorizationException;
        }

        $validatedScores = $this->validateScores($scores);

        return DB::transaction(function () use (
            $submission,
            $assessorRole,
            $lecturerId,
            $validatedScores,
        ): DefenseAssessment {
            DefenseSubmission::query()
                ->whereKey($submission->id)
                ->lockForUpdate()
                ->firstOrFail();

            return DefenseAssessment::query()->updateOrCreate(
                [
                    'defense_submission_id' => $submission->id,
                    'assessor_role' => $assessorRole,
                ],
                [
                    'lecturer_id' => $lecturerId,
                    ...$validatedScores,
                ],
            );
        });
    }

    public function assessorRole(User $user, DefenseSubmission $submission): ?string
    {
        $lecturerId = $user->lecturer?->id;

        if (! $lecturerId) {
            return null;
        }

        if ($submission->student?->dpm_id === $lecturerId) {
            return 'dpm';
        }

        if ($submission->dosen_penguji_1_id === $lecturerId) {
            return 'penguji_1';
        }

        if ($submission->dosen_penguji_2_id === $lecturerId) {
            return 'penguji_2';
        }

        return null;
    }

    public function assessmentFor(User $user, DefenseSubmission $submission): ?DefenseAssessment
    {
        $role = $this->assessorRole($user, $submission);

        if (! $role) {
            return null;
        }

        if ($submission->relationLoaded('assessments')) {
            return $submission->assessments->firstWhere('assessor_role', $role);
        }

        return $submission->assessments()
            ->where('assessor_role', $role)
            ->first();
    }

    public function weightedScore(DefenseAssessment $assessment): float
    {
        return round(
            ((float) $assessment->internship_performance_score * self::INTERNSHIP_PERFORMANCE_WEIGHT)
            + ((float) $assessment->final_report_score * self::FINAL_REPORT_WEIGHT)
            + ((float) $assessment->presentation_score * self::PRESENTATION_WEIGHT),
            2,
        );
    }

    public function finalScore(DefenseSubmission $submission): ?float
    {
        $dpmScore = $this->scoreForRole($submission, 'dpm');
        $examinerAverage = $this->examinerAverageScore($submission);

        if ($dpmScore === null || $examinerAverage === null) {
            return null;
        }

        return round(($dpmScore + ($examinerAverage * 2)) / 3, 2);
    }

    public function examinerAverageScore(DefenseSubmission $submission): ?float
    {
        $examinerOneScore = $this->scoreForRole($submission, 'penguji_1');
        $examinerTwoScore = $this->scoreForRole($submission, 'penguji_2');

        if ($examinerOneScore === null || $examinerTwoScore === null) {
            return null;
        }

        return round(($examinerOneScore + $examinerTwoScore) / 2, 2);
    }

    public function scoreForRole(DefenseSubmission $submission, string $role): ?float
    {
        $assessment = $submission->relationLoaded('assessments')
            ? $submission->assessments->firstWhere('assessor_role', $role)
            : $submission->assessments()->where('assessor_role', $role)->first();

        return $assessment ? $this->weightedScore($assessment) : null;
    }

    public function letterGrade(float $score): string
    {
        return match (true) {
            $score >= 85 => 'A',
            $score >= 80 => 'A-',
            $score >= 75 => 'B+',
            $score >= 70 => 'B',
            $score >= 65 => 'C+',
            $score >= 60 => 'C',
            $score >= 50 => 'D',
            default => 'E',
        };
    }

    public function completedAssessorCount(DefenseSubmission $submission): int
    {
        $assessments = $submission->relationLoaded('assessments')
            ? $submission->assessments
            : $submission->assessments()->get();

        return $assessments
            ->whereIn('assessor_role', ['dpm', 'penguji_1', 'penguji_2'])
            ->unique('assessor_role')
            ->count();
    }

    /**
     * @param  array<string, float|int|string>  $scores
     * @return array{
     *     internship_performance_score: float,
     *     final_report_score: float,
     *     presentation_score: float
     * }
     */
    private function validateScores(array $scores): array
    {
        $validated = [];
        $labels = [
            'internship_performance_score' => 'Nilai Kinerja Magang',
            'final_report_score' => 'Nilai Laporan Akhir',
            'presentation_score' => 'Nilai Ujian Presentasi',
        ];

        foreach ($labels as $field => $label) {
            $value = $scores[$field] ?? null;

            if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                throw ValidationException::withMessages([
                    $field => "{$label} harus berupa angka antara 0 sampai 100.",
                ]);
            }

            $validated[$field] = round((float) $value, 2);
        }

        return $validated;
    }
}
