<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipMitraApplicantResource\Pages\ListMitraApplicants;
use App\Filament\Resources\Ppaip\PpaipMitraApplicantResource\Pages\ViewMitraApplicant;
use App\Models\Application;
use App\Support\AccessStatus;
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

class PpaipMitraApplicantResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Pelamar Mitra';

    protected static ?string $navigationGroup = 'Review';

    protected static ?string $modelLabel = 'Pelamar Mitra';

    protected static ?string $pluralModelLabel = 'Pelamar Mitra';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'ppaip/mitra-applicants';

    public static function canAccess(): bool
    {
        return Auth::user()?->isPpaip() ?? false;
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
        return parent::getEloquentQuery()
            ->with([
                'student:id,nim,name,study_program,email,semester,jumlah_sks,ipk,access_status',
                'internship:id,company_name,position,location,deadline',
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Mahasiswa')
                    ->schema([
                        TextEntry::make('student.nim')->label('NIM'),
                        TextEntry::make('student.name')->label('Nama'),
                        TextEntry::make('student.email')->label('Email'),
                        TextEntry::make('student.study_program')->label('Program Studi'),
                        TextEntry::make('student.semester')->label('Semester')->placeholder('-'),
                        TextEntry::make('student.jumlah_sks')->label('Jumlah SKS')->placeholder('-'),
                        TextEntry::make('student.ipk')->label('IPK')->placeholder('-'),
                        TextEntry::make('student.access_status')
                            ->label('Status Mahasiswa')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => AccessStatus::label($state))
                            ->color(fn (?string $state): string => AccessStatus::color($state)),
                    ])
                    ->columns(2),

                Section::make('Lamaran Mitra')
                    ->schema([
                        TextEntry::make('internship.company_name')->label('Perusahaan')->placeholder('-'),
                        TextEntry::make('internship.position')->label('Posisi')->placeholder('-'),
                        TextEntry::make('internship.location')->label('Lokasi')->placeholder('-'),
                        TextEntry::make('internship.deadline')->label('Deadline')->date('d M Y')->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status Lamaran')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Applied' => 'info',
                                'Accepted' => 'success',
                                'RejectedByCompany', 'Canceled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')->label('Tanggal Lamar')->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Berkas')
                    ->schema([
                        TextEntry::make('cv_document')
                            ->label('CV')
                            ->state(fn (Application $record): HtmlString => self::cvLinks($record))
                            ->html(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.study_program')
                    ->label('Prodi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('internship.company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('internship.position')
                    ->label('Posisi')
                    ->searchable()
                    ->limit(32),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Lamaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Applied' => 'info',
                        'Accepted' => 'success',
                        'RejectedByCompany', 'Canceled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('cv_file_path')
                    ->label('CV')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Lamar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Lamaran')
                    ->options([
                        'Applied' => 'Dilamar',
                        'Accepted' => 'Diterima',
                        'RejectedByCompany' => 'Ditolak Perusahaan',
                        'Canceled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options([
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Informatika' => 'Informatika',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['value'] ?? null,
                            fn (Builder $query, string $value): Builder => $query->whereHas(
                                'student',
                                fn (Builder $query): Builder => $query->where('study_program', $value),
                            ),
                        )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('downloadCv')
                    ->label('Unduh CV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Application $record): bool => ! empty($record->cv_file_path))
                    ->url(fn (Application $record): string => route('mitra-applications.cv.download', $record)),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMitraApplicants::route('/'),
            'view' => ViewMitraApplicant::route('/{record}'),
        ];
    }

    private static function cvLinks(Application $record): HtmlString
    {
        if (! $record->cv_file_path) {
            return new HtmlString('<span class="text-gray-400">Belum diunggah</span>');
        }

        $filename = e(basename($record->cv_file_path));
        $previewUrl = e(route('mitra-applications.cv.preview', $record));
        $downloadUrl = e(route('mitra-applications.cv.download', $record));

        return new HtmlString(
            '<div class="flex flex-wrap items-center gap-3">'.
                '<span class="font-medium text-gray-700">'.$filename.'</span>'.
                '<a href="'.$previewUrl.'" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">Preview</a>'.
                '<a href="'.$downloadUrl.'" class="text-sm text-blue-600 hover:underline">Download</a>'.
            '</div>'
        );
    }
}
