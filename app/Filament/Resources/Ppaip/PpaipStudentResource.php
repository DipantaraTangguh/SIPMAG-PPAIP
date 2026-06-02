<?php

namespace App\Filament\Resources\Ppaip;

use App\Models\Student;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpaipStudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Mahasiswa (Semua Prodi)';
    protected static ?string $navigationGroup = 'Data';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'ppaip/students';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'ppaip';
    }
    public static function canCreate(): bool
    {
        return false;
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
                Tables\Columns\IconColumn::make('is_independent')->label('Mandiri')->boolean(),
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
                        'SiklusSelesai'   => 'SiklusSelesai',
                    ]),
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options([
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Informatika'      => 'Informatika',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Ppaip\PpaipStudentResource\Pages\ListStudents::route('/'),
        ];
    }
}
