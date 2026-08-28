<?php

namespace App\Filament\Resources\Kaprodi;

use App\Filament\Resources\Kaprodi\KaprodiStudentResource\Pages\ListStudents;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Services\DpmAssignmentService;
use App\Services\StudentStateMachine;
use App\Support\AccessStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class KaprodiStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Mahasiswa';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $modelLabel = 'Mahasiswa';

    protected static ?string $pluralModelLabel = 'Mahasiswa';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'kaprodi/students';

    private static function currentUser(): ?User
    {
        return Auth::user();
    }

    public static function canAccess(): bool
    {
        return static::currentUser()?->role === 'kaprodi';
    }

    public static function getEloquentQuery(): Builder
    {
        $prodi = static::currentUser()?->resolveStudyProgram();

        return parent::getEloquentQuery()
            ->where('study_program', $prodi)
            ->with(['dpm', 'sidangSubmission', 'supervisorApplication'])
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Mahasiswa')->schema([
                Forms\Components\TextInput::make('nim')->required()->maxLength(20)->disabled(),
                Forms\Components\TextInput::make('name')->label('Nama')->required()->disabled(),
                Forms\Components\TextInput::make('email')->email()->disabled(),
                Forms\Components\TextInput::make('study_program')->label('Program Studi')->disabled(),
                Forms\Components\Select::make('access_status')
                    ->label('Status Akses')
                    ->options(AccessStatus::options())
                    ->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Form 1 Data')->schema([
                // Catatan opsional dari mahasiswa; ditarik keluar dari KeyValue
                // supaya terbaca utuh saat Kaprodi meninjau pengajuan.
                Forms\Components\Placeholder::make('catatan_khusus')
                    ->label('Catatan Khusus')
                    ->content(fn (?Student $record): string => $record?->form1_data['catatanKhusus'] ?? 'Tidak ada catatan'),
                Forms\Components\KeyValue::make('form1_data')->label('Data Form 1')->disabled(),
                Forms\Components\Textarea::make('form1_rejection_reason')
                    ->label('Alasan Penolakan')
                    ->disabled(),
            ])->collapsible(),
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
                Tables\Columns\TextColumn::make('catatan_khusus')
                    ->label('Catatan Khusus')
                    ->getStateUsing(fn (Student $record): ?string => $record->form1_data['catatanKhusus'] ?? null)
                    ->placeholder('-')
                    ->limit(30)
                    ->tooltip(fn (Student $record): ?string => $record->form1_data['catatanKhusus'] ?? null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('dpm.lecturer_name')->label('DPM')->placeholder('-'),
                Tables\Columns\TextColumn::make('approved_logbook_count')->label('Logbook')->sortable(),
                Tables\Columns\IconColumn::make('supervisorApplication.loa_path')
                    ->label('LoA')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_status')
                    ->label('Status')
                    ->options(AccessStatus::options()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Preview LoA via modal biar Kaprodi bisa verifikasi keaslian sebelum tunjuk DPM.
                Tables\Actions\Action::make('previewLoa')
                    ->label('Lihat LoA')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Student $record) => ! empty($record->supervisorApplication?->loa_path))
                    ->modalHeading(fn (Student $record) => 'LoA - '.$record->name.' ('.$record->nim.')')
                    ->modalContent(function (Student $record): HtmlString {
                        $previewUrl = route('kaprodi.loa.preview', $record);
                        $downloadUrl = route('kaprodi.loa.download', $record);
                        $ext = pathinfo($record->supervisorApplication->loa_path, PATHINFO_EXTENSION);
                        $isPdf = strtolower($ext) === 'pdf';

                        if ($isPdf) {
                            $preview = '<iframe src="'.$previewUrl.'" class="w-full rounded-lg border" style="height:70vh;"></iframe>';
                        } else {
                            $preview = '<img src="'.$previewUrl.'" alt="LoA" class="max-w-full max-h-[70vh] mx-auto rounded-lg shadow" />';
                        }

                        return new HtmlString(
                            '<div class="space-y-4">'.
                                $preview.
                                '<div class="flex justify-end">'.
                                    '<a href="'.$downloadUrl.'" '.
                                       'class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-primary-700 transition">'.
                                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>'.
                                        'Unduh LoA'.
                                    '</a>'.
                                '</div>'.
                            '</div>'
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('5xl'),

                // Action approve Form 1.
                Tables\Actions\Action::make('approveForm1')
                    ->label('Setujui Form 1')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Student $record) => $record->access_status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(function (Student $record) {
                        $user = static::currentUser();

                        // Nama dan NIDN di Surat Keterangan diambil dari
                        // form1_approved_by. Untuk Staff Prodi yang disimpan
                        // adalah dosen Kaprodi-nya, supaya surat tetap atas
                        // nama pejabat yang berwenang.
                        $signatory = $user?->signatoryLecturer();

                        if (! $signatory) {
                            Notification::make()
                                ->title('Penandatangan surat tidak dapat ditentukan')
                                ->body('Pastikan program studi ini punya tepat satu akun Kaprodi.')
                                ->danger()
                                ->send();

                            return;
                        }

                        app(StudentStateMachine::class)->transition($record, 'ApprovedForm1', [
                            'form1_rejection_reason' => null,
                            'form1_approved_by' => $signatory->id,
                            'form1_approved_at' => now(),
                        ]);
                    }),

                // Action reject Form 1.
                Tables\Actions\Action::make('rejectForm1')
                    ->label('Tolak Form 1')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Student $record) => $record->access_status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan Penolakan')->required(),
                    ])
                    ->action(function (Student $record, array $data): void {
                        app(StudentStateMachine::class)->transition($record, 'RejectedForm1', [
                            'form1_rejection_reason' => $data['reason'],
                        ]);
                    }),

                // Action assign DPM setelah pengajuan pembimbing masuk.
                // Tombol ini muncul cuma kalau mahasiswa sudah submit pengajuan.
                // Dan belum punya DPM supaya assign-nya nggak dobel.
                Tables\Actions\Action::make('assignDpm')
                    ->label('Tunjuk DPM')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Student $record) => ! $record->dpm_id &&
                        $record->access_status === 'HasApplication' &&
                        $record->supervisorApplication()->exists()
                    )
                    ->modalHeading('Assign Dosen Pembimbing Magang')
                    ->form(function (Student $record): array {
                        $supApp = $record->supervisorApplication;
                        $fields = [];

                        // Tampilkan konteks praktisi supaya Kaprodi yakin sebelum assign.
                        if ($supApp) {
                            $mulai = '-';
                            if ($supApp->mulai_magang) {
                                try {
                                    $mulai = Carbon::parse($supApp->mulai_magang)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $mulai = (string) $supApp->mulai_magang;
                                }
                            }

                            $selesai = '-';
                            if ($supApp->selesai_magang) {
                                try {
                                    $selesai = Carbon::parse($supApp->selesai_magang)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $selesai = (string) $supApp->selesai_magang;
                                }
                            }

                            $diajukan = '-';
                            if ($supApp->submitted_at) {
                                try {
                                    $diajukan = Carbon::parse($supApp->submitted_at)->format('d/m/Y H:i');
                                } catch (\Throwable $e) {
                                    $diajukan = (string) $supApp->submitted_at;
                                }
                            }

                            $periode = "{$mulai} s/d {$selesai}";

                            $fields[] = Forms\Components\Placeholder::make('nomination_info')
                                ->label(new HtmlString('<strong>Pengajuan Pembimbing</strong>'))
                                ->content(new HtmlString(
                                    "<strong>Perusahaan:</strong> {$supApp->company_name}<br />".
                                    '<strong>Lingkup Magang:</strong> '.($supApp->lingkup_magang ?? '-').'<br />'.
                                    '<strong>Nama Praktisi:</strong> '.($supApp->nama_praktisi ?? $supApp->company_contact).'<br />'.
                                    '<strong>Jabatan:</strong> '.($supApp->jabatan_praktisi ?? '-').'<br />'.
                                    '<strong>No. Telepon:</strong> '.($supApp->no_telepon ?? '-').'<br />'.
                                    '<strong>Email:</strong> '.($supApp->email ?? '-').'<br />'.
                                    "<strong>Periode:</strong> {$periode}<br />".
                                    "<strong>Diajukan:</strong> {$diajukan}"
                                ));
                        }

                        $kaprodiProdi = static::currentUser()?->resolveStudyProgram();

                        $fields[] = Forms\Components\Select::make('dpm_id')
                            ->label('Pilih DPM')
                            ->options(
                                Lecturer::query()
                                    ->when(
                                        $kaprodiProdi,
                                        fn ($query) => $query->eligibleDpmForStudyProgram($kaprodiProdi),
                                        fn ($query) => $query->whereRaw('1 = 0'),
                                    )
                                    ->pluck('lecturer_name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required();

                        return $fields;
                    })
                    ->action(fn (Student $record, array $data) => app(DpmAssignmentService::class)->assign($record, (int) $data['dpm_id'])
                    ),

                // Action jadwalkan sidang.
                // Muncul kalau berkas sidang sudah masuk tapi belum dijadwalkan.
                Tables\Actions\Action::make('scheduleSidang')
                    ->label('Jadwalkan Sidang')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->visible(fn (Student $record) => $record->access_status === 'AwaitingDefense' &&
                        $record->sidangSubmission &&
                        $record->sidangSubmission->status === 'Pending'
                    )
                    ->modalHeading('Jadwalkan Sidang Magang')
                    ->modalDescription('Tetapkan jadwal pelaksanaan sidang dan dosen penguji.')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->label('Tanggal Sidang')
                            ->required()
                            ->minDate(now()->addDay()),
                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Waktu Sidang')
                            ->seconds(false),
                        Forms\Components\TextInput::make('room')
                            ->label('Ruangan / Link')
                            ->maxLength(100),
                        Forms\Components\Select::make('dosen_penguji_1_id')
                            ->label('Dosen Penguji 1')
                            ->required()
                            ->options(function (Student $record) {
                                $prodi = static::currentUser()?->resolveStudyProgram();

                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->when($record->dpm_id, fn ($q) => $q->where('id', '!=', $record->dpm_id))
                                    ->pluck('lecturer_name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('dosen_penguji_2_id')
                            ->label('Dosen Penguji 2')
                            ->required()
                            ->different('dosen_penguji_1_id')
                            ->options(function (Student $record) {
                                $prodi = static::currentUser()?->resolveStudyProgram();

                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->when($record->dpm_id, fn ($q) => $q->where('id', '!=', $record->dpm_id))
                                    ->pluck('lecturer_name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Student $record, array $data) {
                        $lecturerId = static::currentUser()?->signatoryLecturer()?->id;

                        $record->sidangSubmission->update([
                            'status' => 'Scheduled',
                            'scheduled_date' => $data['scheduled_date'],
                            'scheduled_time' => $data['scheduled_time'] ?? null,
                            'room' => $data['room'] ?? null,
                            'dosen_penguji_1_id' => $data['dosen_penguji_1_id'],
                            'dosen_penguji_2_id' => $data['dosen_penguji_2_id'],
                            'scheduled_by' => $lecturerId,
                            'scheduled_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Jadwal sidang berhasil ditetapkan')
                            ->body("Sidang untuk {$record->name} telah dijadwalkan.")
                            ->success()
                            ->send();
                    }),

            ])
            ->bulkActions([
                // Jadwalkan sidang serentak (tanggal/waktu/ruang/penguji sama)
                // buat semua mahasiswa terpilih yang sudah siap sidang.
                Tables\Actions\BulkAction::make('scheduleSidangBulk')
                    ->label('Jadwalkan Sidang Serentak')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading('Jadwalkan Sidang Serentak')
                    ->modalDescription('Jadwal, ruangan, dan penguji berikut akan diterapkan ke semua mahasiswa terpilih yang siap sidang.')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_date')
                            ->label('Tanggal Sidang')
                            ->required()
                            ->minDate(now()->addDay()),
                        Forms\Components\TimePicker::make('scheduled_time')
                            ->label('Waktu Sidang')
                            ->seconds(false),
                        Forms\Components\TextInput::make('room')
                            ->label('Ruangan / Link')
                            ->maxLength(100),
                        Forms\Components\Select::make('dosen_penguji_1_id')
                            ->label('Dosen Penguji 1')
                            ->required()
                            ->options(function () {
                                $prodi = static::currentUser()?->resolveStudyProgram();

                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->pluck('lecturer_name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('dosen_penguji_2_id')
                            ->label('Dosen Penguji 2')
                            ->required()
                            ->different('dosen_penguji_1_id')
                            ->options(function () {
                                $prodi = static::currentUser()?->resolveStudyProgram();

                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->pluck('lecturer_name', 'id');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $lecturerId = static::currentUser()?->signatoryLecturer()?->id;

                        $eligible = $records->filter(fn (Student $s) => $s->access_status === 'AwaitingDefense' &&
                            $s->sidangSubmission &&
                            $s->sidangSubmission->status === 'Pending'
                        );

                        foreach ($eligible as $student) {
                            $student->sidangSubmission->update([
                                'status' => 'Scheduled',
                                'scheduled_date' => $data['scheduled_date'],
                                'scheduled_time' => $data['scheduled_time'] ?? null,
                                'room' => $data['room'] ?? null,
                                'dosen_penguji_1_id' => $data['dosen_penguji_1_id'],
                                'dosen_penguji_2_id' => $data['dosen_penguji_2_id'],
                                'scheduled_by' => $lecturerId,
                                'scheduled_at' => now(),
                            ]);
                        }

                        $skipped = $records->count() - $eligible->count();
                        $body = "{$eligible->count()} sidang berhasil dijadwalkan.";
                        if ($skipped > 0) {
                            $body .= " {$skipped} mahasiswa dilewati (belum siap sidang).";
                        }

                        Notification::make()
                            ->title('Jadwal sidang serentak diterapkan')
                            ->body($body)
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
        ];
    }
}
