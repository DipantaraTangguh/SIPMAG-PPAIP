<?php

namespace App\Filament\Resources\Dpm\DpmLogbookResource\RelationManagers;

use App\Models\Logbook;
use App\Services\LogbookReviewService;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
                        'danger' => 'Rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'PendingReview' => 'Menunggu Review',
                        'Approved' => 'Disetujui',
                        'Rejected' => 'Ditolak',
                        default => $state,
                    }),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PendingReview' => 'Menunggu Review',
                        'Approved' => 'Disetujui',
                        'Rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                // Action approve logbook DPM.
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Logbook $record) => $record->status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(function (Logbook $record) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        app(LogbookReviewService::class)->approve($record->id, $user->lecturer->id);
                    }),

                // Action reject logbook DPM.
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Logbook $record) => $record->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Catatan Penolakan'),
                    ])
                    ->action(function (Logbook $record, array $data) {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();
                        app(LogbookReviewService::class)->reject($record->id, $user->lecturer->id, $data['note'] ?? null);
                    }),
            ])
            ->bulkActions([]);
    }
}
