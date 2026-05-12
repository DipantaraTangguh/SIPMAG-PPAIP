<?php

namespace App\Filament\Resources;

use App\Models\Logbook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LogbookResource extends Resource
{
    protected static ?string $model = Logbook::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Logbook';
    protected static ?string $navigationGroup = 'Bimbingan';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Mahasiswa')
                ->relationship('student', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\DatePicker::make('tanggal')->required(),
            Forms\Components\Textarea::make('kegiatan_harian')->label('Kegiatan Harian')->required()->rows(3),
            Forms\Components\Textarea::make('hasil')->label('Hasil')->required()->rows(3),
            Forms\Components\Select::make('status')
                ->options([
                    'PendingReview' => 'Pending Review',
                    'Approved'      => 'Approved',
                    'Rejected'      => 'Rejected',
                ])
                ->default('PendingReview'),
            Forms\Components\Textarea::make('dpm_note')->label('Catatan DPM')->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nim')->label('NIM')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
                Tables\Columns\TextColumn::make('kegiatan_harian')->label('Kegiatan')->limit(40),
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
                    ->options(['PendingReview' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Logbook $r) => $r->status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(function (Logbook $record) {
                        $record->update(['status' => 'Approved', 'dpm_note' => null]);
                        $student = $record->student;
                        $student->increment('approved_logbook_count');
                        if ($student->approved_logbook_count >= 6 && $student->access_status === 'HasDPM') {
                            $student->update(['access_status' => 'LogbookComplete']);
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Logbook $r) => $r->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('note')->label('Catatan')->nullable(),
                    ])
                    ->action(fn (Logbook $record, array $data) => $record->update([
                        'status'   => 'Rejected',
                        'dpm_note' => $data['note'] ?? null,
                    ])),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\LogbookResource\Pages\ListLogbooks::route('/'),
            'edit'  => \App\Filament\Resources\LogbookResource\Pages\EditLogbook::route('/{record}/edit'),
        ];
    }
}
