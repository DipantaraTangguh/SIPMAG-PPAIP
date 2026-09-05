<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipStudentResource\Pages\ListStudents;
use App\Models\Student;
use App\Services\DefenseAssessmentService;
use App\Support\AccessStatus;
use App\Support\StudyProgram;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PpaipStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Mahasiswa (Semua Prodi)';

    protected static ?string $navigationGroup = 'Data';

    protected static ?string $modelLabel = 'Mahasiswa';

    protected static ?string $pluralModelLabel = 'Mahasiswa';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'ppaip/students';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'ppaip';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['dpm', 'sidangSubmission.assessments', 'supervisorApplication'])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')]);
    }

    /**
     * Isi modal "Lihat".
     *
     * Sebelumnya resource ini memasang ViewAction tanpa form, infolist,
     * maupun halaman view, sehingga modalnya terbuka tanpa isi apa pun.
     *
     * Yang ditampilkan sengaja hanya yang belum terbaca di tabel. Sembilan
     * kolomnya sudah memuat status, DPM, jumlah logbook, dan nilai akhir,
     * jadi sisanya -- data akademik, isi Form 1, serta tempat dan periode
     * magang -- yang dikumpulkan di sini. PPAIP satu-satunya peran yang
     * mengawasi lintas program studi, dan detail Form 1 tidak muncul di
     * layar mana pun lagi untuk mereka.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Data Akademik')
                ->schema([
                    TextEntry::make('nim')->label('NIM'),
                    TextEntry::make('name')->label('Nama'),
                    TextEntry::make('study_program')->label('Program Studi'),
                    TextEntry::make('email')->label('Email')->placeholder('-'),
                    TextEntry::make('semester')->label('Semester')->placeholder('-'),
                    TextEntry::make('tahun_akademik')->label('Tahun Akademik')->placeholder('-'),
                    TextEntry::make('jumlah_sks')->label('Jumlah SKS')->placeholder('-'),
                    TextEntry::make('ipk')->label('IPK')->placeholder('-'),
                ])
                ->columns(2),

            Section::make('Pengajuan Form 1')
                ->schema([
                    TextEntry::make('form1_data.jenisMagang')
                        ->label('Jenis Magang')
                        ->formatStateUsing(fn (?string $state): string => $state === 'non_wajib' ? 'Non-Wajib' : 'Wajib')
                        ->placeholder('-'),
                    TextEntry::make('form1_data.skemaMagang')->label('Skema Magang')->placeholder('-'),
                    TextEntry::make('form1_data.topikMagang')->label('Topik / Tempat')->placeholder('-'),
                    TextEntry::make('form1_data.outputTarget')->label('Target Output')->placeholder('-'),
                    TextEntry::make('form1_data.catatanKhusus')
                        ->label('Catatan Khusus')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull(),
                    TextEntry::make('form1_approved_at')
                        ->label('Disetujui Pada')
                        ->dateTime('d M Y H:i')
                        ->placeholder('Belum disetujui'),
                    TextEntry::make('form1_rejection_reason')
                        ->label('Alasan Penolakan')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            // Hanya terisi untuk mahasiswa yang sudah mengajukan pembimbing.
            // Jalur non-wajib berhenti sebelum tahap ini, jadi seksinya
            // disembunyikan daripada menampilkan deretan tanda hubung.
            Section::make('Tempat & Periode Magang')
                ->schema([
                    TextEntry::make('supervisorApplication.company_name')->label('Perusahaan'),
                    TextEntry::make('supervisorApplication.nama_praktisi')
                        ->label('Praktisi Pembimbing')
                        ->placeholder('-'),
                    TextEntry::make('supervisorApplication.lingkup_magang')
                        ->label('Lingkup Magang')
                        ->placeholder('-')
                        ->columnSpanFull(),
                    TextEntry::make('supervisorApplication.mulai_magang')->label('Mulai')->date('d M Y'),
                    TextEntry::make('supervisorApplication.selesai_magang')->label('Selesai')->date('d M Y'),
                ])
                ->columns(2)
                ->visible(fn (Student $record): bool => $record->supervisorApplication !== null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('study_program')->label('Prodi')->sortable(),
                Tables\Columns\TextColumn::make('access_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AccessStatus::label($state))
                    ->color(fn (string $state): string => AccessStatus::color($state)),
                Tables\Columns\TextColumn::make('dpm.lecturer_name')->label('DPM')->placeholder('-'),
                Tables\Columns\TextColumn::make('approved_logbook_count')->label('Logbook')->sortable(),
                Tables\Columns\TextColumn::make('assessment_progress')
                    ->label('Penilaian Sidang')
                    ->getStateUsing(function (Student $record): string {
                        $submission = $record->sidangSubmission;

                        if (! $submission || $submission->status !== 'Scheduled') {
                            return '-';
                        }

                        $count = app(DefenseAssessmentService::class)->completedAssessorCount($submission);

                        return "{$count}/3";
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            '3/3' => 'success',
                            '-' => 'gray',
                            default => 'warning',
                        };
                    }),
                Tables\Columns\TextColumn::make('defense_final_score')
                    ->label('Nilai Akhir')
                    ->getStateUsing(function (Student $record): string {
                        $submission = $record->sidangSubmission;

                        if (! $submission) {
                            return '-';
                        }

                        $service = app(DefenseAssessmentService::class);
                        $score = $service->finalScore($submission);

                        return $score === null
                            ? 'Belum tersedia'
                            : number_format($score, 2).' ('.$service->letterGrade($score).')';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Belum tersedia' || $state === '-' ? 'gray' : 'success'),
                Tables\Columns\IconColumn::make('is_independent')->label('Mandiri')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_status')
                    ->label('Status')
                    ->options(AccessStatus::options()),
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options(StudyProgram::options()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
        ];
    }
}
