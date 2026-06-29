<?php

namespace App\Support;

use App\Models\DefenseSubmission;

final class DefenseDocument
{
    private const DOCUMENTS = [
        'laporan' => [
            'attribute' => 'laporan_path',
            'label' => 'Laporan Magang',
        ],
        'poster' => [
            'attribute' => 'poster_path',
            'label' => 'Poster Presentasi',
        ],
        'foto_kegiatan_1' => [
            'attribute' => 'foto_kegiatan_1_path',
            'label' => 'Foto Kegiatan 1',
        ],
        'foto_kegiatan_2' => [
            'attribute' => 'foto_kegiatan_2_path',
            'label' => 'Foto Kegiatan 2',
        ],
    ];

    /**
     * @return array<string>
     */
    public static function keys(): array
    {
        return array_keys(self::DOCUMENTS);
    }

    public static function label(string $document): string
    {
        return self::DOCUMENTS[$document]['label'] ?? $document;
    }

    public static function storedPath(DefenseSubmission $submission, string $document): ?string
    {
        $attribute = self::DOCUMENTS[$document]['attribute'] ?? null;

        return $attribute ? $submission->{$attribute} : null;
    }

    public static function resolvedPath(DefenseSubmission $submission, string $document): ?string
    {
        return StoredFilePath::resolve(
            storage_path('app/private'),
            self::storedPath($submission, $document),
        );
    }
}
