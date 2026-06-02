<?php

namespace App\Filament\Resources\Dpm;

use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DpmLogbookResource extends Resource
{
    protected static ?string $model = Student::class;
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
     * Scope to students assigned to this DPM.
     */
    public static function getEloquentQuery(): Builder
    {
        $lecturerId = auth()->user()?->lecturer?->id;

        return parent::getEloquentQuery()
            ->where('dpm_id', $lecturerId);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('study_program')
                    ->label('Prodi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('logbooks_count')
                    ->label('Total Logbook')
                    ->counts('logbooks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pending_count')
                    ->label('Menunggu Review')
                    ->getStateUsing(fn (Student $record) => $record->logbooks()->where('status', 'PendingReview')->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('approved_logbook_count')
                    ->label('Disetujui')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\Action::make('viewLogbooks')
                    ->label('Lihat Logbook')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Student $record) => static::getUrl('logbooks', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            DpmLogbookResource\RelationManagers\LogbooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'    => DpmLogbookResource\Pages\ListLogbooks::route('/'),
            'logbooks' => DpmLogbookResource\Pages\ViewStudentLogbooks::route('/{record}/logbooks'),
        ];
    }
}
