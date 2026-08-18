<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipForm1Resource\Pages\ListForm1Submissions;
use App\Filament\Resources\Ppaip\PpaipForm1Resource\Pages\ViewForm1Submission;
use App\Models\Student;
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

class PpaipForm1Resource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Form 1';

    protected static ?string $navigationGroup = 'Review';

    protected static ?string $modelLabel = 'Pengajuan Form 1';

    protected static ?string $pluralModelLabel = 'Pengajuan Form 1';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'ppaip/form1';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->isPpaip() ?? false;
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
            ->whereNotNull('form1_data')
            ->with('form1Approver');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Mahasiswa')
                    ->schema([
                        TextEntry::make('nim')->label('NIM'),
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('study_program')->label('Program Studi'),
                        TextEntry::make('access_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => AccessStatus::label($state))
                            ->color(fn (string $state): string => AccessStatus::color($state)),
                    ])
                    ->columns(2),

                Section::make('Data Form 1')
                    ->schema([
                        TextEntry::make('semester_form1')
                            ->label('Semester')
                            ->state(fn (Student $record): string => (string) ($record->form1_data['semester'] ?? '-')),
                        TextEntry::make('jumlah_sks_form1')
                            ->label('Jumlah SKS')
                            ->state(fn (Student $record): string => (string) ($record->form1_data['jumlahSKS'] ?? '-')),
                        TextEntry::make('ipk_form1')
                            ->label('IPK')
                            ->state(fn (Student $record): string => (string) ($record->form1_data['ipk'] ?? '-')),
                        TextEntry::make('skema_magang')
                            ->label('Rencana Skema Magang')
                            ->state(fn (Student $record): string => $record->form1_data['skemaMagang'] ?? '-'),
                        TextEntry::make('topik_magang')
                            ->label('Topik / Lingkup Magang')
                            ->state(fn (Student $record): string => $record->form1_data['topikMagang'] ?? '-')
                            ->columnSpanFull(),
                        TextEntry::make('output_target')
                            ->label('Output')
                            ->state(fn (Student $record): string => $record->form1_data['outputTarget'] ?? '-'),
                        TextEntry::make('catatan_khusus')
                            ->label('Catatan Khusus')
                            ->state(fn (Student $record): ?string => $record->form1_data['catatanKhusus'] ?? null)
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),
                        TextEntry::make('form1Approver.lecturer_name')
                            ->label('Disetujui Oleh')
                            ->placeholder('-'),
                        TextEntry::make('form1_approved_at')
                            ->label('Tanggal Persetujuan')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('form1_rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nim')->label('NIM')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('study_program')->label('Prodi')->sortable(),
                Tables\Columns\TextColumn::make('skema_magang')
                    ->label('Skema')
                    ->getStateUsing(fn (Student $record): string => $record->form1_data['skemaMagang'] ?? '-'),
                Tables\Columns\TextColumn::make('topik_magang')
                    ->label('Topik / Lingkup')
                    ->getStateUsing(fn (Student $record): string => $record->form1_data['topikMagang'] ?? '-')
                    ->limit(32),
                Tables\Columns\TextColumn::make('output_target')
                    ->label('Output')
                    ->getStateUsing(fn (Student $record): string => $record->form1_data['outputTarget'] ?? '-'),
                Tables\Columns\TextColumn::make('catatan_khusus')
                    ->label('Catatan Khusus')
                    ->getStateUsing(fn (Student $record): ?string => $record->form1_data['catatanKhusus'] ?? null)
                    ->placeholder('-')
                    ->limit(32)
                    ->tooltip(fn (Student $record): ?string => $record->form1_data['catatanKhusus'] ?? null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('access_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AccessStatus::label($state))
                    ->color(fn (string $state): string => AccessStatus::color($state)),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_status')
                    ->label('Status')
                    ->options([
                        'PendingReview' => 'Menunggu Review',
                        'ApprovedForm1' => 'Disetujui',
                        'RejectedForm1' => 'Ditolak',
                    ]),
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
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForm1Submissions::route('/'),
            'view' => ViewForm1Submission::route('/{record}'),
        ];
    }

}
