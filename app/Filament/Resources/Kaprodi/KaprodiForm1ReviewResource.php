<?php

namespace App\Filament\Resources\Kaprodi;

use Illuminate\Database\Eloquent\Builder;

/**
 * Tumpukan kerja: Form 1 yang menunggu keputusan Kaprodi.
 *
 * Mewarisi tabel, kolom, dan seluruh aksi dari KaprodiStudentResource --
 * yang berbeda hanya penyaringan barisnya. Menyalin definisi aksinya ke
 * sini akan berarti empat salinan modal Tunjuk DPM dan form Jadwal Sidang
 * yang harus dijaga tetap sama.
 */
class KaprodiForm1ReviewResource extends KaprodiStudentResource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Review Form 1';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $modelLabel = 'Pengajuan Form 1';

    protected static ?string $pluralModelLabel = 'Pengajuan Form 1';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'kaprodi/review-form1';

    public static function getEloquentQuery(): Builder
    {
        return static::whereNeedsForm1Review(parent::getEloquentQuery());
    }

    public static function getNavigationBadge(): ?string
    {
        return static::countPile(static::whereNeedsForm1Review(...));
    }

    public static function getPages(): array
    {
        return [
            'index' => KaprodiForm1ReviewResource\Pages\ListForm1Review::route('/'),
        ];
    }
}
