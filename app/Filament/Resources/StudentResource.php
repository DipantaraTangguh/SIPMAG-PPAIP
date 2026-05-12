<?php

namespace App\Filament\Resources;

use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Mahasiswa';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Mahasiswa')->schema([
                Forms\Components\TextInput::make('nim')->required()->maxLength(20),
                Forms\Components\TextInput::make('name')->label('Nama')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\Select::make('study_program')
                    ->label('Program Studi')
                    ->options(['Sistem Informasi' => 'Sistem Informasi', 'Informatika' => 'Informatika'])
                    ->required(),
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
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Pembimbing & Progress')->schema([
                Forms\Components\Select::make('dpm_id')
                    ->label('Dosen Pembimbing')
                    ->relationship('dpm', 'lecturer_name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('approved_logbook_count')
                    ->label('Logbook Approved')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_independent')->label('Jalur Mandiri'),
            ])->columns(3),

            Forms\Components\Section::make('Form 1')->schema([
                Forms\Components\KeyValue::make('form1_data')->label('Data Form 1'),
                Forms\Components\Textarea::make('form1_rejection_reason')
                    ->label('Alasan Penolakan')
                    ->nullable(),
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
                Tables\Columns\BadgeColumn::make('access_status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'Unverified',
                        'warning' => 'PendingReview',
                        'danger'  => 'RejectedForm1',
                        'success' => 'ApprovedForm1',
                        'info'    => 'HasApplication',
                        'primary' => 'HasDPM',
                        'success' => 'LogbookComplete',
                        'warning' => 'MenungguSidang',
                    ]),
                Tables\Columns\TextColumn::make('dpm.lecturer_name')->label('DPM')->placeholder('—'),
                Tables\Columns\TextColumn::make('approved_logbook_count')->label('Logbook')->sortable(),
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
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options(['Sistem Informasi' => 'Sistem Informasi', 'Informatika' => 'Informatika']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approveForm1')
                    ->label('Approve Form 1')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Student $record) => $record->access_status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(fn (Student $record) => $record->update([
                        'access_status'          => 'ApprovedForm1',
                        'form1_rejection_reason' => null,
                    ])),
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
                Tables\Actions\Action::make('assignDpm')
                    ->label('Assign DPM')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Student $record) => in_array($record->access_status, ['HasApplication', 'ApprovedForm1']))
                    ->form([
                        Forms\Components\Select::make('dpm_id')
                            ->label('Pilih DPM')
                            ->relationship('dpm', 'lecturer_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(fn (Student $record, array $data) => $record->update([
                        'dpm_id'        => $data['dpm_id'],
                        'access_status' => 'HasDPM',
                    ])),
                Tables\Actions\Action::make('completeCycle')
                    ->label('Selesaikan Siklus')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Student $record) => $record->access_status === 'MenungguSidang')
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
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\StudentResource\Pages\ListStudents::route('/'),
            'edit'  => \App\Filament\Resources\StudentResource\Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
