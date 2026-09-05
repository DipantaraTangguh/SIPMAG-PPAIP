<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipForm2Resource\Pages\ListForm2;
use App\Filament\Resources\Ppaip\PpaipForm2Resource\Pages\ViewForm2Submission;
use App\Models\Form2Submission;
use App\Models\User;
use App\Services\Form2DecisionService;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PpaipForm2Resource extends Resource
{
    protected static ?string $model = Form2Submission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Form 2 (Mandiri)';

    protected static ?string $navigationGroup = 'Review';

    protected static ?string $modelLabel = 'Pengajuan Form 2';

    protected static ?string $pluralModelLabel = 'Pengajuan Form 2';

    // Paling atas di grupnya: ini satu-satunya entri PPAIP yang menuntut
    // keputusan. Form 1 dan Pelamar Mitra di bawahnya hanya untuk dilihat.
    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'ppaip/form2';

    private static function currentUser(): ?User
    {
        return Auth::user();
    }

    public static function canAccess(): bool
    {
        return static::currentUser()?->role === 'ppaip';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Jumlah pengajuan yang menunggu keputusan PPAIP.
     *
     * Sengaja memakai syarat yang sama persis dengan tombol Setujui/Tolak
     * (status PendingReview) supaya angkanya selalu sama dengan jumlah baris
     * yang benar-benar bisa ditindak. Dipakai badge sidebar maupun widget
     * dashboard, lewat satu jalur ini, supaya keduanya tidak bisa berbeda.
     */
    public static function pendingCount(): int
    {
        return static::getModel()::where('status', 'PendingReview')->count();
    }

    /**
     * null menyembunyikan badge saat tidak ada pekerjaan, jadi angka nol
     * tidak ikut memenuhi layar.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::pendingCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('student.dpm');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Mahasiswa')
                    ->schema([
                        TextEntry::make('student.nim')->label('NIM'),
                        TextEntry::make('student.name')->label('Nama'),
                        TextEntry::make('student.study_program')->label('Program Studi'),
                        TextEntry::make('student.dpm.lecturer_name')
                            ->label('Dosen Pembimbing')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Data Form 2')
                    ->schema([
                        TextEntry::make('company_name')->label('Perusahaan'),
                        TextEntry::make('nama_pimpinan')->label('Nama Pimpinan')->placeholder('-'),
                        TextEntry::make('jabatan_pimpinan')->label('Jabatan Pimpinan')->placeholder('-'),
                        TextEntry::make('alamat_perusahaan')
                            ->label('Alamat Perusahaan')
                            ->columnSpanFull(),
                        TextEntry::make('lingkup_magang')
                            ->label('Lingkup Magang')
                            ->columnSpanFull(),
                        TextEntry::make('tanggal_mulai')->label('Bulan Mulai')->date('F Y'),
                        TextEntry::make('tanggal_selesai')->label('Bulan Selesai')->date('F Y'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PendingReview' => 'warning',
                                'ApprovedForm2' => 'success',
                                'RejectedForm2' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('submitted_at')
                            ->label('Diajukan Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.study_program')->label('Prodi'),
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('nama_pimpinan')->label('Pimpinan')->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('jabatan_pimpinan')->label('Jabatan')->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('lingkup_magang')->label('Lingkup')->limit(30),
                Tables\Columns\TextColumn::make('tanggal_mulai')->label('Bulan Mulai')->date('M Y'),
                Tables\Columns\TextColumn::make('tanggal_selesai')->label('Bulan Selesai')->date('M Y'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PendingReview' => 'warning',
                        'ApprovedForm2' => 'success',
                        'RejectedForm2' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PendingReview' => 'Menunggu Review',
                        'ApprovedForm2' => 'Disetujui',
                        'RejectedForm2' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Form2Submission $record) => $record->status === 'PendingReview')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Form 2')
                    ->modalDescription(fn (Form2Submission $record): string => ($record->student?->form1_data['jenisMagang'] ?? 'wajib') === 'non_wajib'
                        ? 'Magang non-wajib: mahasiswa akan diminta konfirmasi penerimaan (upload LoA), lalu siklus selesai tanpa tahap DPM/sidang.'
                        : 'Magang wajib: mahasiswa akan lanjut ke tahap pengajuan dosen pembimbing (DPM).')
                    // Logika keputusan terpusat di Form2DecisionService (dipakai
                    // juga endpoint API) - jangan tulis transisi manual di sini.
                    ->action(fn (Form2Submission $record) => app(Form2DecisionService::class)->approve($record)),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Form2Submission $record) => $record->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan Penolakan')->required(),
                    ])
                    ->action(fn (Form2Submission $record, array $data) => app(Form2DecisionService::class)->reject($record, $data['reason'])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForm2::route('/'),
            'view' => ViewForm2Submission::route('/{record}'),
        ];
    }
}
