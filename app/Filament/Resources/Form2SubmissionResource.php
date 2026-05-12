<?php

namespace App\Filament\Resources;

use App\Models\Form2Submission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Form2SubmissionResource extends Resource
{
    protected static ?string $model = Form2Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Form 2 (Mandiri)';
    protected static ?string $navigationGroup = 'Portal';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Mahasiswa')
                ->relationship('student', 'name')
                ->searchable()
                ->preload()
                ->disabled(),
            Forms\Components\TextInput::make('company_name')->label('Perusahaan')->disabled(),
            Forms\Components\TextInput::make('contact_person_name')->label('Kontak PIC')->disabled(),
            Forms\Components\TextInput::make('contact_person_role')->label('Jabatan PIC')->disabled(),
            Forms\Components\TextInput::make('contact_info')->label('Info Kontak')->disabled(),
            Forms\Components\Textarea::make('lingkup_magang')->label('Lingkup Magang')->disabled(),
            Forms\Components\DatePicker::make('tanggal_mulai')->label('Mulai')->disabled(),
            Forms\Components\DatePicker::make('tanggal_selesai')->label('Selesai')->disabled(),
            Forms\Components\Select::make('status')
                ->options([
                    'PendingReview'  => 'Pending Review',
                    'ApprovedForm2'  => 'Approved',
                    'RejectedForm2'  => 'Rejected',
                ]),
            Forms\Components\Textarea::make('rejection_reason')->label('Alasan Penolakan')->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.nim')->label('NIM')->searchable(),
                Tables\Columns\TextColumn::make('student.name')->label('Mahasiswa')->searchable(),
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('lingkup_magang')->label('Lingkup')->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'PendingReview',
                        'success' => 'ApprovedForm2',
                        'danger'  => 'RejectedForm2',
                    ]),
                Tables\Columns\TextColumn::make('submitted_at')->label('Diajukan')->dateTime()->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['PendingReview' => 'Pending', 'ApprovedForm2' => 'Approved', 'RejectedForm2' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Form2Submission $r) => $r->status === 'PendingReview')
                    ->requiresConfirmation()
                    ->action(fn (Form2Submission $r) => $r->update([
                        'status' => 'ApprovedForm2',
                        'rejection_reason' => null,
                    ])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Form2Submission $r) => $r->status === 'PendingReview')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan')->required(),
                    ])
                    ->action(fn (Form2Submission $r, array $data) => $r->update([
                        'status'           => 'RejectedForm2',
                        'rejection_reason' => $data['reason'],
                    ])),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Form2SubmissionResource\Pages\ListForm2Submissions::route('/'),
        ];
    }
}
