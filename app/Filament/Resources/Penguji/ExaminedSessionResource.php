<?php

namespace App\Filament\Resources\Penguji;

use App\Filament\Resources\Penguji\ExaminedSessionResource\Pages\ListExaminedSessions;
use App\Filament\Resources\Penguji\ExaminedSessionResource\Pages\ViewExaminedSession;
use App\Models\DefenseAssessment;
use App\Models\DefenseSubmission;
use App\Models\User;
use App\Services\DefenseAssessmentService;
use App\Support\DefenseDocument;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ExaminedSessionResource extends Resource
{
    protected static ?string $model = DefenseSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Examined Sessions';

    protected static ?string $navigationGroup = 'Sessions';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'penguji/defenses';

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        $lecturerId = $user?->lecturer?->id;

        if (! $lecturerId) {
            return false;
        }

        return $user->isDpm()
            || $user->isDosenPenguji()
            || DefenseSubmission::query()->assessableBy($lecturerId)->exists();
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $lecturerId = Auth::user()?->lecturer?->id;

        return parent::getEloquentQuery()
            ->when(
                $lecturerId,
                fn (Builder $query) => $query->assessableBy($lecturerId),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
            )
            ->with([
                'student:id,dpm_id,nim,name,study_program,email,access_status',
                'examinerOne:id,lecturer_name,nidn',
                'examinerTwo:id,lecturer_name,nidn',
                'assessments:id,defense_submission_id,lecturer_id,assessor_role,internship_performance_score,final_report_score,presentation_score',
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Student Details')
                    ->schema([
                        TextEntry::make('student.name')
                            ->label('Student name'),
                        TextEntry::make('student.nim')
                            ->label('Student ID'),
                        TextEntry::make('student.study_program')
                            ->label('Study program'),
                        TextEntry::make('student.email')
                            ->label('Email'),
                        TextEntry::make('student.access_status')
                            ->label('Workflow status')
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Session Schedule')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Session ID'),
                        TextEntry::make('examiner_id')
                            ->label('Examiner ID')
                            ->state(fn (): ?int => Auth::user()?->lecturer?->id),
                        TextEntry::make('examiner_position')
                            ->label('Assessor position')
                            ->state(fn (DefenseSubmission $record): string => self::assessorPosition($record)),
                        TextEntry::make('scheduled_date')
                            ->label('Examiner date')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('scheduled_time')
                            ->label('Examiner time')
                            ->placeholder('-'),
                        TextEntry::make('room')
                            ->label('Room/link')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Examiner status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Scheduled' => 'success',
                                'Pending' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('scheduled_at')
                            ->label('Scheduled at')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Penilaian Saya')
                    ->description('Setiap komponen dinilai pada skala 0-100.')
                    ->schema([
                        TextEntry::make('my_internship_performance_score')
                            ->label('Kinerja Magang (50%)')
                            ->state(fn (DefenseSubmission $record): string => self::weightedComponentState(
                                self::currentAssessment($record),
                                'internship_performance_score',
                                DefenseAssessmentService::INTERNSHIP_PERFORMANCE_WEIGHT,
                            )),
                        TextEntry::make('my_final_report_score')
                            ->label('Laporan Akhir (30%)')
                            ->state(fn (DefenseSubmission $record): string => self::weightedComponentState(
                                self::currentAssessment($record),
                                'final_report_score',
                                DefenseAssessmentService::FINAL_REPORT_WEIGHT,
                            )),
                        TextEntry::make('my_presentation_score')
                            ->label('Ujian Presentasi Hasil Magang (20%)')
                            ->state(fn (DefenseSubmission $record): string => self::weightedComponentState(
                                self::currentAssessment($record),
                                'presentation_score',
                                DefenseAssessmentService::PRESENTATION_WEIGHT,
                            )),
                        TextEntry::make('my_weighted_score')
                            ->label('Nilai Akhir Saya')
                            ->state(function (DefenseSubmission $record): string {
                                $assessment = self::currentAssessment($record);

                                if (! $assessment) {
                                    return 'Belum dinilai';
                                }

                                $service = app(DefenseAssessmentService::class);
                                $score = $service->weightedScore($assessment);

                                return number_format($score, 2).' ('.$service->letterGrade($score).')';
                            })
                            ->badge()
                            ->color(fn (DefenseSubmission $record): string => self::currentAssessment($record) ? 'success' : 'warning'),
                    ])
                    ->columns(2),

                Section::make('Rekap Nilai Sidang')
                    ->description('Nilai akhir tersedia setelah DPM, Penguji 1, dan Penguji 2 selesai menilai.')
                    ->schema([
                        TextEntry::make('assessment_progress')
                            ->label('Kelengkapan Penilaian')
                            ->state(function (DefenseSubmission $record): string {
                                $count = app(DefenseAssessmentService::class)->completedAssessorCount($record);

                                return "{$count} dari 3 penilai";
                            })
                            ->badge()
                            ->color(fn (DefenseSubmission $record): string => app(DefenseAssessmentService::class)->completedAssessorCount($record) === 3 ? 'success' : 'warning'),
                        TextEntry::make('examiner_average_score')
                            ->label('Rata-rata Dua Penguji')
                            ->state(function (DefenseSubmission $record): string {
                                $score = app(DefenseAssessmentService::class)->examinerAverageScore($record);

                                return $score === null ? 'Menunggu kedua penguji' : number_format($score, 2);
                            }),
                        TextEntry::make('defense_final_score')
                            ->label('Nilai Akhir Sidang')
                            ->state(function (DefenseSubmission $record): string {
                                $score = app(DefenseAssessmentService::class)->finalScore($record);

                                return $score === null ? 'Menunggu seluruh penilai' : number_format($score, 2);
                            }),
                        TextEntry::make('defense_letter_grade')
                            ->label('Nilai Huruf')
                            ->state(function (DefenseSubmission $record): string {
                                $service = app(DefenseAssessmentService::class);
                                $score = $service->finalScore($record);

                                return $score === null ? '-' : $service->letterGrade($score);
                            })
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make('Exam Documents')
                    ->schema([
                        TextEntry::make('laporan_document')
                            ->label(DefenseDocument::label('laporan'))
                            ->state(fn (DefenseSubmission $record): HtmlString => self::documentLinks($record, 'laporan'))
                            ->html(),
                        TextEntry::make('poster_document')
                            ->label(DefenseDocument::label('poster'))
                            ->state(fn (DefenseSubmission $record): HtmlString => self::documentLinks($record, 'poster'))
                            ->html(),
                        TextEntry::make('foto_kegiatan_1_document')
                            ->label(DefenseDocument::label('foto_kegiatan_1'))
                            ->state(fn (DefenseSubmission $record): HtmlString => self::documentLinks($record, 'foto_kegiatan_1'))
                            ->html(),
                        TextEntry::make('foto_kegiatan_2_document')
                            ->label(DefenseDocument::label('foto_kegiatan_2'))
                            ->state(fn (DefenseSubmission $record): HtmlString => self::documentLinks($record, 'foto_kegiatan_2'))
                            ->html(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.nim')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.study_program')
                    ->label('Study program')
                    ->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Session ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_examiner_id')
                    ->label('Examiner ID')
                    ->getStateUsing(fn (): ?int => Auth::user()?->lecturer?->id),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->label('Examiner date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->label('Examiner time')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('room')
                    ->label('Room/link')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Examiner status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Scheduled' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('examiner_position')
                    ->label('Assessor position')
                    ->getStateUsing(fn (DefenseSubmission $record): string => self::assessorPosition($record))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'DPM' => 'success',
                        'Examiner 1' => 'info',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('assessment_status')
                    ->label('Assessment')
                    ->getStateUsing(fn (DefenseSubmission $record): string => self::currentAssessment($record) ? 'Sudah dinilai' : 'Belum dinilai')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Sudah dinilai' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('my_score')
                    ->label('My score')
                    ->getStateUsing(function (DefenseSubmission $record): string {
                        $assessment = self::currentAssessment($record);

                        return $assessment
                            ? number_format(app(DefenseAssessmentService::class)->weightedScore($assessment), 2)
                            : '-';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Scheduled' => 'Scheduled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('scheduled_date');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExaminedSessions::route('/'),
            'view' => ViewExaminedSession::route('/{record}'),
        ];
    }

    private static function assessorPosition(DefenseSubmission $record): string
    {
        /** @var User|null $user */
        $user = Auth::user();
        $role = $user ? app(DefenseAssessmentService::class)->assessorRole($user, $record) : null;

        return $role?->label() ?? '-';
    }

    public static function currentAssessment(DefenseSubmission $record): ?DefenseAssessment
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user
            ? app(DefenseAssessmentService::class)->assessmentFor($user, $record)
            : null;
    }

    private static function weightedComponentState(
        ?DefenseAssessment $assessment,
        string $field,
        float $weight,
    ): string {
        if (! $assessment) {
            return 'Belum dinilai';
        }

        $score = (float) $assessment->{$field};
        $weightedScore = round($score * $weight, 2);

        return number_format($score, 2)
            .' x '.number_format($weight * 100, 0).'% = '
            .number_format($weightedScore, 2);
    }

    private static function documentLinks(DefenseSubmission $record, string $document): HtmlString
    {
        $storedPath = DefenseDocument::storedPath($record, $document);

        if (! $storedPath) {
            return new HtmlString('<span class="text-gray-400">Not uploaded</span>');
        }

        $filename = e(basename($storedPath));
        $previewUrl = e(route('defense-documents.preview', ['submission' => $record, 'document' => $document]));
        $downloadUrl = e(route('defense-documents.download', ['submission' => $record, 'document' => $document]));

        return new HtmlString(
            '<div class="flex flex-wrap items-center gap-3">'.
                '<span class="font-medium text-gray-700">'.$filename.'</span>'.
                '<a href="'.$previewUrl.'" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">Preview</a>'.
                '<a href="'.$downloadUrl.'" class="text-sm text-blue-600 hover:underline">Download</a>'.
            '</div>'
        );
    }
}
