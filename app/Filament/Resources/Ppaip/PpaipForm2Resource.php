<?php

namespace App\Filament\Resources\Ppaip;

use App\Filament\Resources\Ppaip\PpaipForm2Resource\Pages\ListForm2;
use App\Models\Form2Submission;
use App\Models\User;
use Filament\Forms;
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

    protected static ?int $navigationSort = 1;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('student.dpm');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.study_program')->label('Prodi'),
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('nama_pimpinan')->label('Pimpinan')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('jabatan_pimpinan')->label('Jabatan')->placeholder('—')->toggleable(),
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
                    ->modalDescription('Menyetujui Form 2 akan mengubah status mahasiswa menjadi HasApplication.')
                    ->action(function (Form2Submission $record) {
                        $record->update([
                            'status' => 'ApprovedForm2',
                            'rejection_reason' => null,
                        ]);
                        $student = $record->student;
                        if ($student && $student->access_status === 'ApprovedForm1') {
                            $student->access_status = 'HasApplication';
                            $student->save();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Form2Submission $record) => $record->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan Penolakan')->required(),
                    ])
                    ->action(fn (Form2Submission $record, array $data) => $record->update([
                        'status' => 'RejectedForm2',
                        'rejection_reason' => $data['reason'],
                    ])),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForm2::route('/'),
        ];
    }
}
