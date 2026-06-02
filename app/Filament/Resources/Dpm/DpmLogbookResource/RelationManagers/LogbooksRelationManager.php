<?php

namespace App\Filament\Resources\Dpm\DpmLogbookResource\RelationManagers;

use App\Models\Logbook;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LogbooksRelationManager extends RelationManager
{
    protected static string $relationship = 'logbooks';
    protected static ?string $title = 'Daftar Logbook';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kegiatan_harian')
                    ->label('Kegiatan Harian')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('hasil')
                    ->label('Hasil')
                    ->limit(40),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'PendingReview',
                        'success' => 'Approved',
                        'danger'  => 'Rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'PendingReview' => 'Menunggu Review',
                        'Approved'      => 'Disetujui',
                        'Rejected'      => 'Ditolak',
                        default         => $state,
                    }),
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
}
