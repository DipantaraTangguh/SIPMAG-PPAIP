<?php

namespace App\Filament\Resources\Shared;

use App\Support\StudyProgram;
use App\Filament\Resources\Shared\InternshipCycleResource\Pages\ListInternshipCycles;
use App\Filament\Resources\Shared\InternshipCycleResource\Pages\ViewInternshipCycle;
use App\Models\InternshipCycle;
use App\Models\User;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Rekap seluruh siklus magang yang sudah selesai (wajib maupun non-wajib).
 *
 * Sumbernya tabel append-only internship_cycles, jadi satu-satunya tampilan
 * yang tetap utuh setelah mahasiswa mereset siklus dan mendaftar magang lagi.
 * PPAIP melihat semua prodi; Kaprodi otomatis dibatasi prodinya sendiri.
 */
class InternshipCycleResource extends Resource
{
    protected static ?string $model = InternshipCycle::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Rekap Magang';

    protected static ?string $navigationGroup = 'Rekap';

    protected static ?string $modelLabel = 'Riwayat Magang';

    protected static ?string $pluralModelLabel = 'Rekap Magang';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'internship-cycles';

    private static function currentUser(): ?User
    {
        return Auth::user();
    }

    public static function canAccess(): bool
    {
        return in_array(static::currentUser()?->role, ['ppaip', 'kaprodi'], true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $user = static::currentUser();
        $query = parent::getEloquentQuery();

        // Kaprodi hanya boleh melihat prodinya sendiri; pakai snapshot
        // study_program di baris riwayat, bukan prodi mahasiswa saat ini.
        if ($user?->role === 'kaprodi') {
            $prodi = $user->lecturer?->study_program;

            return $prodi
                ? $query->where('study_program', $prodi)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Identitas Mahasiswa')
                ->description('Data direkam saat siklus selesai, bukan data terkini.')
                ->schema([
                    TextEntry::make('nim')->label('NIM'),
                    TextEntry::make('nama')->label('Nama'),
                    TextEntry::make('study_program')->label('Program Studi'),
                    TextEntry::make('semester')->label('Semester')->placeholder('-'),
                    TextEntry::make('ipk')->label('IPK')->placeholder('-'),
                    TextEntry::make('cycle_number')->label('Siklus Ke-'),
                ])
                ->columns(3),

            Section::make('Rencana Magang (Form 1)')
                ->schema([
                    TextEntry::make('jenis_magang')
                        ->label('Jenis Magang')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => $state === 'wajib' ? 'Wajib' : 'Non-Wajib')
                        ->color(fn (string $state): string => $state === 'wajib' ? 'primary' : 'info'),
                    TextEntry::make('skema_magang')->label('Skema')->placeholder('-'),
                    TextEntry::make('output_target')->label('Target Output')->placeholder('-'),
                    TextEntry::make('topik_magang')
                        ->label('Topik / Lingkup')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->columns(3),

            Section::make('Tempat & Periode Magang')
                ->schema([
                    TextEntry::make('company_name')->label('Perusahaan')->placeholder('-'),
                    TextEntry::make('nama_pimpinan')->label('Pimpinan')->placeholder('-'),
                    TextEntry::make('tanggal_mulai')->label('Mulai')->date('F Y')->placeholder('-'),
                    TextEntry::make('tanggal_selesai')->label('Selesai')->date('F Y')->placeholder('-'),
                    TextEntry::make('alamat_perusahaan')
                        ->label('Alamat')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('loa_path')
                        ->label('Bukti LoA')
                        ->placeholder('Belum diunggah')
                        ->formatStateUsing(fn (?string $state, InternshipCycle $record): HtmlString => new HtmlString(
                            $state
                                ? '<a href="'.e(route('internship-cycles.loa.preview', $record)).'" target="_blank" rel="noopener" class="text-primary-600 underline">Pratinjau</a>'
                                    .' &middot; '
                                    .'<a href="'.e(route('internship-cycles.loa.download', $record)).'" class="text-primary-600 underline">Unduh</a>'
                                : '<span class="text-gray-400">Belum diunggah</span>'
                        ))
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Hasil')
                ->schema([
                    TextEntry::make('outcome_status')
                        ->label('Status Akhir')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => $state === 'CycleCompleted'
                            ? 'Selesai (Sidang & Penilaian)'
                            : 'Selesai (Non-Wajib)')
                        ->color('success'),
                    TextEntry::make('final_score')
                        ->label('Nilai Akhir')
                        ->placeholder('Tidak dinilai (non-wajib)')
                        ->formatStateUsing(fn (?float $state, InternshipCycle $record): string => $state === null
                            ? '-'
                            : number_format($state, 2).' ('.$record->letter_grade.')'),
                    TextEntry::make('completed_at')
                        ->label('Diselesaikan Pada')
                        ->dateTime('d M Y H:i')
                        ->placeholder('-'),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')->label('NIM')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('study_program')->label('Prodi')->sortable(),
                Tables\Columns\TextColumn::make('cycle_number')->label('Siklus')->sortable(),
                Tables\Columns\TextColumn::make('jenis_magang')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'wajib' ? 'Wajib' : 'Non-Wajib')
                    ->color(fn (string $state): string => $state === 'wajib' ? 'primary' : 'info'),
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('tanggal_mulai')->label('Mulai')->date('M Y')->placeholder('-'),
                Tables\Columns\TextColumn::make('tanggal_selesai')->label('Selesai')->date('M Y')->placeholder('-'),
                Tables\Columns\TextColumn::make('final_score')
                    ->label('Nilai Akhir')
                    ->sortable()
                    ->badge()
                    ->color(fn (?string $state): string => $state === null ? 'gray' : 'success')
                    ->formatStateUsing(fn (?float $state, InternshipCycle $record): string => $state === null
                        ? '-'
                        : number_format($state, 2).' ('.$record->letter_grade.')'),
                Tables\Columns\IconColumn::make('loa_path')
                    ->label('LoA')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Diselesaikan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('completed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options(StudyProgram::options())
                    // Kaprodi sudah terkunci ke prodinya lewat getEloquentQuery.
                    ->visible(fn (): bool => static::currentUser()?->role === 'ppaip'),
                Tables\Filters\SelectFilter::make('jenis_magang')
                    ->label('Jenis Magang')
                    ->options([
                        'wajib' => 'Wajib',
                        'non_wajib' => 'Non-Wajib',
                    ]),
                Tables\Filters\SelectFilter::make('outcome_status')
                    ->label('Status Akhir')
                    ->options([
                        'CycleCompleted' => 'Selesai (Sidang & Penilaian)',
                        'ElectiveCompleted' => 'Selesai (Non-Wajib)',
                    ]),
                Tables\Filters\Filter::make('periode_selesai')
                    ->label('Periode Selesai')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('sampai')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari'] ?? null, fn ($q, $date) => $q->whereDate('completed_at', '>=', $date))
                        ->when($data['sampai'] ?? null, fn ($q, $date) => $q->whereDate('completed_at', '<=', $date))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInternshipCycles::route('/'),
            'view' => ViewInternshipCycle::route('/{record}'),
        ];
    }
}
