<?php

namespace App\Filament\Resources\Kaprodi;

use App\Models\Student;
use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class KaprodiStudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Mahasiswa';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'kaprodi/students';

    /**
     * Only Kaprodi can see this resource.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'kaprodi';
    }

    /**
     * Scope queries to the Kaprodi's own study program.
     */
    public static function getEloquentQuery(): Builder
    {
        $prodi = auth()->user()?->lecturer?->study_program;

        return parent::getEloquentQuery()
            ->where('study_program', $prodi)
            ->with('sidangSubmission');
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
                    ->options([
                        'Unverified'      => 'Unverified',
                        'PendingReview'   => 'PendingReview',
                        'RejectedForm1'   => 'RejectedForm1',
                        'ApprovedForm1'   => 'ApprovedForm1',
                        'HasApplication'  => 'HasApplication',
                        'HasDPM'          => 'HasDPM',
                        'LogbookComplete' => 'LogbookComplete',
                        'MenungguSidang'  => 'MenungguSidang',
                        'SiklusSelesai'   => 'SiklusSelesai',
                    ])
                    ->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Form 1 Data')->schema([
                Forms\Components\KeyValue::make('form1_data')->label('Data Form 1')->disabled(),
                Forms\Components\Textarea::make('form1_rejection_reason')
                    ->label('Alasan Penolakan')
                    ->disabled(),
                Forms\Components\Placeholder::make('transkrip_status')
                    ->label('Transkrip Nilai')
                    ->content(function (?Student $record): HtmlString {
                        if (!$record || !$record->form1_pdf_path) {
                            return new HtmlString('<span class="text-gray-400">Belum diunggah</span>');
                        }
                        $previewUrl = route('transkrip.preview', $record->id);
                        $downloadUrl = route('transkrip.download', $record->id);
                        return new HtmlString(
                            '<div class="flex items-center gap-3">' .
                            '<span class="text-green-600 font-medium">✅ ' . basename($record->form1_pdf_path) . '</span>' .
                            '<a href="' . $previewUrl . '" target="_blank" class="text-sm text-blue-600 hover:underline">Lihat</a>' .
                            '<a href="' . $downloadUrl . '" class="text-sm text-blue-600 hover:underline">Download</a>' .
                            '</div>'
                        );
                    }),
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
                    ->color(fn (string $state): string => match ($state) {
                        'Unverified' => 'gray',
                        'PendingReview', 'MenungguSidang' => 'warning',
                        'RejectedForm1' => 'danger',
                        'ApprovedForm1', 'LogbookComplete' => 'success',
                        'HasApplication' => 'info',
                        'HasDPM' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('dpm.lecturer_name')->label('DPM')->placeholder('—'),
                Tables\Columns\TextColumn::make('approved_logbook_count')->label('Logbook')->sortable(),
                Tables\Columns\IconColumn::make('form1_pdf_path')
                    ->label('Transkrip')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_status')
                    ->label('Status')
                    ->options([
                        'Unverified'      => 'Unverified',
                        'PendingReview'   => 'PendingReview',
                        'ApprovedForm1'   => 'ApprovedForm1',
                        'HasApplication'  => 'HasApplication',
                        'HasDPM'          => 'HasDPM',
                        'LogbookComplete' => 'LogbookComplete',
                        'MenungguSidang'  => 'MenungguSidang',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ── Preview Transkrip (modal with iframe + download link) ──
                Tables\Actions\Action::make('previewTranskrip')
                    ->label('Lihat Transkrip')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Student $record) => !empty($record->form1_pdf_path))
                    ->modalHeading(fn (Student $record) => 'Transkrip — ' . $record->name . ' (' . $record->nim . ')')
                    ->modalContent(function (Student $record): HtmlString {
                        $previewUrl = route('transkrip.preview', $record->id);
                        $downloadUrl = route('transkrip.download', $record->id);
                        $ext = pathinfo($record->form1_pdf_path, PATHINFO_EXTENSION);
                        $isPdf = strtolower($ext) === 'pdf';

                        if ($isPdf) {
                            $preview = '<iframe src="' . $previewUrl . '" class="w-full rounded-lg border" style="height:70vh;"></iframe>';
                        } else {
                            $preview = '<img src="' . $previewUrl . '" alt="Transkrip" class="max-w-full max-h-[70vh] mx-auto rounded-lg shadow" />';
                        }

                        return new HtmlString(
                            '<div class="space-y-4">' .
                                $preview .
                                '<div class="flex justify-end">' .
                                    '<a href="' . $downloadUrl . '" ' .
                                       'class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-primary-700 transition">' .
                                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>' .
                                        'Download Transkrip' .
                                    '</a>' .
                                '</div>' .
                            '</div>'
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('5xl'),

                // ── Form 1: Approve ──
                Tables\Actions\Action::make('approveForm1')
                    ->label('Approve Form 1')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Student $record) => $record->access_status === 'PendingReview')
                    ->requiresConfirmation()
                    ->before(function (Tables\Actions\Action $action) {
                        $lecturer = auth()->user()?->lecturer;
                        if (!$lecturer || !$lecturer->signature_path) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tanda tangan digital belum diunggah')
                                ->body('Anda harus mengunggah tanda tangan digital terlebih dahulu melalui menu "Profil Saya" sebelum dapat menyetujui Form 1.')
                                ->danger()
                                ->persistent()
                                ->send();
                            $action->cancel();
                        }
                    })
                    ->action(function (Student $record) {
                        $lecturerId = auth()->user()?->lecturer?->id;
                        $record->update([
                            'access_status'          => 'ApprovedForm1',
                            'form1_rejection_reason' => null,
                            'form1_approved_by'      => $lecturerId,
                            'form1_approved_at'      => now(),
                        ]);
                    }),

                // ── Form 1: Reject ──
                Tables\Actions\Action::make('rejectForm1')
                    ->label('Reject Form 1')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Student $record) => $record->access_status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan Penolakan')->required(),
                    ])
                    ->action(fn (Student $record, array $data) => $record->update([
                        'access_status'          => 'RejectedForm1',
                        'form1_rejection_reason' => $data['reason'],
                    ])),

                // ── Assign DPM ──
                // Only visible when student has submitted a supervisor nomination
                // (i.e. has a record in supervisor_applications) and doesn't have a DPM yet.
                Tables\Actions\Action::make('assignDpm')
                    ->label('Assign DPM')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Student $record) =>
                        !$record->dpm_id &&
                        $record->supervisorApplication()->exists()
                    )
                    ->modalHeading('Assign Dosen Pembimbing Magang')
                    ->form(function (Student $record): array {
                        $supApp = $record->supervisorApplication;
                        $fields = [];

                        // Show supervisor nomination context
                        if ($supApp) {
                            $mulai = '-';
                            if ($supApp->mulai_magang) {
                                try {
                                    $mulai = \Illuminate\Support\Carbon::parse($supApp->mulai_magang)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $mulai = (string) $supApp->mulai_magang;
                                }
                            }

                            $selesai = '-';
                            if ($supApp->selesai_magang) {
                                try {
                                    $selesai = \Illuminate\Support\Carbon::parse($supApp->selesai_magang)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $selesai = (string) $supApp->selesai_magang;
                                }
                            }

                            $diajukan = '-';
                            if ($supApp->submitted_at) {
                                try {
                                    $diajukan = \Illuminate\Support\Carbon::parse($supApp->submitted_at)->format('d/m/Y H:i');
                                } catch (\Throwable $e) {
                                    $diajukan = (string) $supApp->submitted_at;
                                }
                            }

                            $periode = "{$mulai} s/d {$selesai}";

                            $fields[] = Forms\Components\Placeholder::make('nomination_info')
                                ->label(new HtmlString('<strong>Pengajuan Pembimbing</strong>'))
                                ->content(new HtmlString(
                                    "<strong>Perusahaan:</strong> {$supApp->company_name}<br />" .
                                    "<strong>Nama Praktisi:</strong> " . ($supApp->nama_praktisi ?? $supApp->company_contact) . "<br />" .
                                    "<strong>Jabatan:</strong> " . ($supApp->jabatan_praktisi ?? '-') . "<br />" .
                                    "<strong>No. Telepon:</strong> " . ($supApp->no_telepon ?? '-') . "<br />" .
                                    "<strong>Email:</strong> " . ($supApp->email ?? '-') . "<br />" .
                                    "<strong>Periode:</strong> {$periode}<br />" .
                                    "<strong>Diajukan:</strong> {$diajukan}"
                                ));
                        }

                        $kaprodiProdi = auth()->user()?->lecturer?->study_program;

                        $fields[] = Forms\Components\Select::make('dpm_id')
                            ->label('Pilih DPM')
                            ->options(
                                Lecturer::whereNotNull('user_id')
                                    ->when($kaprodiProdi, fn ($q) => $q->where('study_program', $kaprodiProdi))
                                    ->pluck('lecturer_name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required();

                        return $fields;
                    })
                    ->action(fn (Student $record, array $data) => $record->update([
                        'dpm_id'        => $data['dpm_id'],
                        'access_status' => 'HasDPM',
                    ])),

                // ── Schedule Sidang ──
                // Visible when student has submitted defense docs but not yet scheduled
                Tables\Actions\Action::make('scheduleSidang')
                    ->label('Jadwalkan Sidang')
                    ->icon('heroicon-o-calendar-days')
                    ->color('info')
                    ->visible(fn (Student $record) =>
                        $record->access_status === 'MenungguSidang' &&
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
                            ->label('Waktu Sidang'),
                        Forms\Components\TextInput::make('room')
                            ->label('Ruangan / Link')
                            ->maxLength(100),
                        Forms\Components\Select::make('dosen_penguji_1')
                            ->label('Dosen Penguji 1')
                            ->required()
                            ->options(function () {
                                $prodi = auth()->user()?->lecturer?->study_program;
                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->pluck('lecturer_name', 'lecturer_name');
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('dosen_penguji_2')
                            ->label('Dosen Penguji 2')
                            ->required()
                            ->options(function () {
                                $prodi = auth()->user()?->lecturer?->study_program;
                                return Lecturer::whereNotNull('user_id')
                                    ->when($prodi, fn ($q) => $q->where('study_program', $prodi))
                                    ->pluck('lecturer_name', 'lecturer_name');
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (Student $record, array $data) {
                        $lecturerId = auth()->user()?->lecturer?->id;

                        $record->sidangSubmission->update([
                            'status'          => 'Scheduled',
                            'scheduled_date'  => $data['scheduled_date'],
                            'scheduled_time'  => $data['scheduled_time'] ?? null,
                            'room'            => $data['room'] ?? null,
                            'dosen_penguji_1' => $data['dosen_penguji_1'],
                            'dosen_penguji_2' => $data['dosen_penguji_2'],
                            'scheduled_by'    => $lecturerId,
                            'scheduled_at'    => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Jadwal sidang berhasil ditetapkan')
                            ->body("Sidang untuk {$record->name} telah dijadwalkan.")
                            ->success()
                            ->send();
                    }),

                // ── Complete Sidang Cycle ──
                // Only visible after sidang has been scheduled
                Tables\Actions\Action::make('completeCycle')
                    ->label('Selesaikan Siklus')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Student $record) =>
                        $record->access_status === 'MenungguSidang' &&
                        $record->sidangSubmission &&
                        $record->sidangSubmission->status === 'Scheduled'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Selesaikan Siklus Magang')
                    ->modalDescription('Siklus magang akan direset. Riwayat tetap tersimpan.')
                    ->action(fn (Student $record) => $record->update([
                        'access_status'          => 'SiklusSelesai',
                        'dpm_id'                 => null,
                        'is_independent'         => false,
                        'approved_logbook_count' => 0,
                        'form1_data'             => null,
                        'form1_pdf_path'         => null,
                        'form1_rejection_reason' => null,
                    ])),
            ])
            ->bulkActions([
                // ── Bulk Download Transkrip ──
                Tables\Actions\BulkAction::make('bulkDownloadTranskrip')
                    ->label('Download Transkrip')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        $students = $records->filter(fn (Student $s) => !empty($s->form1_pdf_path));

                        if ($students->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak ada transkrip')
                                ->body('Mahasiswa yang dipilih belum mengunggah transkrip.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Single file — download directly
                        if ($students->count() === 1) {
                            $student = $students->first();
                            $path = storage_path('app/private/' . $student->form1_pdf_path);
                            if (file_exists($path)) {
                                $ext = pathinfo($path, PATHINFO_EXTENSION);
                                return response()->download($path, "transkrip_{$student->nim}.{$ext}");
                            }
                            return;
                        }

                        // Multiple files — create ZIP
                        $zipName = 'transkrip_' . now()->format('Ymd_His') . '.zip';
                        $zipPath = storage_path('app/private/temp/' . $zipName);

                        if (!is_dir(dirname($zipPath))) {
                            mkdir(dirname($zipPath), 0755, true);
                        }

                        $zip = new \ZipArchive();
                        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                        foreach ($students as $student) {
                            $filePath = storage_path('app/private/' . $student->form1_pdf_path);
                            if (file_exists($filePath)) {
                                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                                $zip->addFile($filePath, "transkrip_{$student->nim}_{$student->name}.{$ext}");
                            }
                        }

                        $zip->close();

                        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Kaprodi\KaprodiStudentResource\Pages\ListStudents::route('/'),
        ];
    }
}
