<?php

namespace App\Filament\Resources\Kaprodi;

use Illuminate\Database\Eloquent\Builder;

/**
 * Tumpukan kerja: pengajuan pembimbing yang menunggu penunjukan DPM.
 *
 * Mewarisi seluruh tabel dan aksi dari KaprodiStudentResource; yang
 * berbeda hanya penyaringan barisnya.
 */
class KaprodiDpmAssignmentResource extends KaprodiStudentResource
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationLabel = 'Tunjuk DPM';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $modelLabel = 'Pengajuan Pembimbing';

    protected static ?string $pluralModelLabel = 'Pengajuan Pembimbing';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'kaprodi/dpm-assignment';

    public static function getEloquentQuery(): Builder
    {
        return static::whereNeedsDpm(parent::getEloquentQuery());
    }

    public static function getNavigationBadge(): ?string
    {
        return static::countPile(static::whereNeedsDpm(...));
    }

    public static function getPages(): array
    {
        return [
            'index' => KaprodiDpmAssignmentResource\Pages\ListDpmAssignment::route('/'),
        ];
    }
}
