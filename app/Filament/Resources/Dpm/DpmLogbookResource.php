<?php

namespace App\Filament\Resources\Dpm;

use App\Models\Logbook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DpmLogbookResource extends Resource
{
    protected static ?string $model = Logbook::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Logbook Bimbingan';
    protected static ?string $navigationGroup = 'Bimbingan';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'dpm/logbooks';

    /**
     * Only DPM can see this resource.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'dpm';
    }

    /**
     * Scope to logbooks of students assigned to this DPM.
     */
    public static function getEloquentQuery(): Builder
    {
        $lecturerId = auth()->user()?->lecturer?->id;

        return parent::getEloquentQuery()
            ->whereHas('student', fn ($q) => $q->where('dpm_id', $lecturerId));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Logbook')->schema([
                Forms\Components\TextInput::make('student.name')->label('Mahasiswa')->disabled(),
                Forms\Components\DatePicker::make('tanggal')->label('Tanggal')->disabled(),
                Forms\Components\Textarea::make('kegiatan_harian')->label('Kegiatan Harian')->disabled(),
                Forms\Components\Textarea::make('hasil')->label('Hasil')->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'PendingReview' => 'Menunggu Review',
                        'Approved'      => 'Disetujui',
                        'Rejected'      => 'Ditolak',
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('dpm_note')->label('Catatan DPM'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('tanggal')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('kegiatan_harian')
                    ->label('Kegiatan')
                    ->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PendingReview',
                        'success' => 'Approved',
                        'danger'  => 'Rejected',
                    ]),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PendingReview' => 'Menunggu Review',
                        'Approved'      => 'Disetujui',
                        'Rejected'      => 'Ditolak',
                    ]),
            ])
            ->actions([
                // ── Approve logbook ──
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Logbook $record) => $record->status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(function (Logbook $record) {
                        $record->update(['status' => 'Approved', 'dpm_note' => null]);
                        $student = $record->student;
                        $student->increment('approved_logbook_count');

                        if ($student->approved_logbook_count >= 6 && $student->access_status === 'HasDPM') {
                            $student->update(['access_status' => 'LogbookComplete']);
                        }
                    }),

                // ── Reject logbook ──
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Logbook $record) => $record->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Catatan Penolakan'),
                    ])
                    ->action(fn (Logbook $record, array $data) => $record->update([
                        'status'   => 'Rejected',
                        'dpm_note' => $data['note'] ?? null,
                    ])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Dpm\DpmLogbookResource\Pages\ListLogbooks::route('/'),
        ];
    }
}
