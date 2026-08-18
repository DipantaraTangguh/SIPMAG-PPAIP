<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipStudentResource\Pages\ListStudents;
use App\Models\Student;
use App\Services\DefenseAssessmentService;
use App\Support\AccessStatus;
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
            ->with(['dpm', 'sidangSubmission.assessments'])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')]);
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
                    ->options([
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Informatika' => 'Informatika',
                    ]),
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
