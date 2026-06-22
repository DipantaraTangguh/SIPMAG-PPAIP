<?php

namespace App\Filament\Resources\Penguji;

use App\Filament\Resources\Penguji\ExaminedSessionResource\Pages\ListExaminedSessions;
use App\Filament\Resources\Penguji\ExaminedSessionResource\Pages\ViewExaminedSession;
use App\Models\DefenseSubmission;
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
        $user = Auth::user();
        $lecturerId = $user?->lecturer?->id;

        if (! $lecturerId) {
            return false;
        }

        return $user->isDosenPenguji()
            || DefenseSubmission::query()
                ->where('dosen_penguji_1_id', $lecturerId)
                ->orWhere('dosen_penguji_2_id', $lecturerId)
                ->exists();
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
                fn (Builder $query) => $query->where(function (Builder $query) use ($lecturerId): void {
                    $query
                        ->where('dosen_penguji_1_id', $lecturerId)
                        ->orWhere('dosen_penguji_2_id', $lecturerId);
                }),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
            )
            ->with([
                'student:id,nim,name,study_program,email,access_status',
                'examinerOne:id,lecturer_name,nidn',
                'examinerTwo:id,lecturer_name,nidn',
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
                            ->label('Examiner position')
                            ->state(fn (DefenseSubmission $record): string => self::examinerPosition($record)),
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
                        TextEntry::make('krs_document')
                            ->label(DefenseDocument::label('krs'))
                            ->state(fn (DefenseSubmission $record): HtmlString => self::documentLinks($record, 'krs'))
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
                    ->label('Examiner position')
                    ->getStateUsing(fn (DefenseSubmission $record): string => self::examinerPosition($record))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Examiner 1' ? 'info' : 'primary'),
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

    private static function examinerPosition(DefenseSubmission $record): string
    {
        $lecturerId = Auth::user()?->lecturer?->id;

        return $record->dosen_penguji_1_id === $lecturerId
            ? 'Examiner 1'
            : 'Examiner 2';
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
