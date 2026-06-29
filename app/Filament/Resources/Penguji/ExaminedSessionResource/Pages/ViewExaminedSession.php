<?php

namespace App\Filament\Resources\Penguji\ExaminedSessionResource\Pages;

use App\Filament\Resources\Penguji\ExaminedSessionResource;
use App\Models\DefenseAssessment;
use App\Models\User;
use App\Services\DefenseAssessmentService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewExaminedSession extends ViewRecord
{
    protected static string $resource = ExaminedSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assess')
                ->label(fn (): string => $this->currentAssessment() ? 'Edit Nilai' : 'Isi Nilai')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => Auth::user()?->can('assess', $this->record) === true)
                ->modalHeading('Penilaian Sidang Magang')
                ->modalDescription('Isi nilai per komponen atau gunakan input cepat untuk menyamakan seluruh komponen.')
                ->fillForm(function (): array {
                    $assessment = $this->currentAssessment();

                    return [
                        'quick_entry' => $assessment === null,
                        'final_score' => $assessment
                            ? app(DefenseAssessmentService::class)->weightedScore($assessment)
                            : null,
                        'internship_performance_score' => $assessment?->internship_performance_score,
                        'final_report_score' => $assessment?->final_report_score,
                        'presentation_score' => $assessment?->presentation_score,
                    ];
                })
                ->form([
                    Forms\Components\Toggle::make('quick_entry')
                        ->label('Gunakan nilai akhir untuk semua komponen')
                        ->helperText('Nilai yang dimasukkan akan otomatis diterapkan ke Kinerja Magang, Laporan Akhir, dan Ujian Presentasi.')
                        ->live(),
                    Forms\Components\TextInput::make('final_score')
                        ->label('Nilai Akhir')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->step(0.01)
                        ->required(fn (Get $get): bool => (bool) $get('quick_entry'))
                        ->visible(fn (Get $get): bool => (bool) $get('quick_entry')),
                    Forms\Components\Section::make('Nilai per Komponen')
                        ->schema([
                            Forms\Components\TextInput::make('internship_performance_score')
                                ->label('Kinerja Magang (50%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->required(),
                            Forms\Components\TextInput::make('final_report_score')
                                ->label('Laporan Akhir (30%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->required(),
                            Forms\Components\TextInput::make('presentation_score')
                                ->label('Ujian Presentasi Hasil Magang (20%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->required(),
                        ])
                        ->columns(3)
                        ->visible(fn (Get $get): bool => ! (bool) $get('quick_entry')),
                ])
                ->action(function (array $data): void {
                    /** @var User $user */
                    $user = Auth::user();

                    if ($data['quick_entry']) {
                        $scores = [
                            'internship_performance_score' => $data['final_score'],
                            'final_report_score' => $data['final_score'],
                            'presentation_score' => $data['final_score'],
                        ];
                    } else {
                        $scores = [
                            'internship_performance_score' => $data['internship_performance_score'],
                            'final_report_score' => $data['final_report_score'],
                            'presentation_score' => $data['presentation_score'],
                        ];
                    }

                    app(DefenseAssessmentService::class)->save($user, $this->record, $scores);
                    $this->record->refresh()->load('student', 'assessments');

                    Notification::make()
                        ->title('Nilai sidang berhasil disimpan')
                        ->body('Nilai masih dapat diedit melalui tombol Edit Nilai.')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function currentAssessment(): ?DefenseAssessment
    {
        return ExaminedSessionResource::currentAssessment($this->record);
    }
}
