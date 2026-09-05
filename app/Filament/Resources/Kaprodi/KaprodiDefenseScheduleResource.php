<?php

namespace App\Filament\Resources\Kaprodi;

use Illuminate\Database\Eloquent\Builder;

/**
 * Tumpukan kerja: berkas sidang yang menunggu penjadwalan.
 *
 * Mewarisi seluruh tabel dan aksi dari KaprodiStudentResource, termasuk
 * aksi massal penjadwalan -- di halaman ini justru paling berguna, karena
 * semua barisnya memang menunggu dijadwalkan.
 */
class KaprodiDefenseScheduleResource extends KaprodiStudentResource
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Sidang';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $modelLabel = 'Sidang Menunggu Jadwal';

    protected static ?string $pluralModelLabel = 'Sidang Menunggu Jadwal';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'kaprodi/jadwal-sidang';

    public static function getEloquentQuery(): Builder
    {
        return static::whereNeedsDefenseSchedule(parent::getEloquentQuery());
    }

    /**
     * Satu-satunya halaman yang memuat penjadwalan serentak: seluruh barisnya
     * memang menunggu dijadwalkan, jadi "pilih semua" langsung bermakna.
     *
     * @return array<int, \Filament\Tables\Actions\BulkAction>
     */
    protected static function pileBulkActions(): array
    {
        return [static::scheduleSidangBulkAction()];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::countPile(static::whereNeedsDefenseSchedule(...));
    }

    public static function getPages(): array
    {
        return [
            'index' => KaprodiDefenseScheduleResource\Pages\ListDefenseSchedule::route('/'),
        ];
    }
}
