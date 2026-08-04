<?php

namespace App\Filament\Resources\Dpm;

use App\Models\Student;
use App\Models\SupervisorApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DpmLogbookResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Logbook Bimbingan';
    protected static ?string $navigationGroup = 'Bimbingan';

    protected static ?string $modelLabel = 'Logbook';

    protected static ?string $pluralModelLabel = 'Logbook';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'dpm/logbooks';
    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'dpm';
    }
    public static function getEloquentQuery(): Builder
    {
        $lecturerId = Auth::user()?->lecturer?->id;

        return parent::getEloquentQuery()
            ->where('dpm_id', $lecturerId)
            ->with('supervisorApplication:id,student_id,loa_path')
            ->withCount(['logbooks as approved_logbook_count' => fn ($query) => $query->where('status', 'Approved')]);
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
                Tables\Columns\IconColumn::make('supervisorApplication.loa_path')
                    ->label('LoA')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\Action::make('viewLogbooks')
                    ->label('Lihat Logbook')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Student $record) => static::getUrl('logbooks', ['record' => $record])),
                Tables\Actions\Action::make('viewLoa')
                    ->label('Lihat LoA')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn (Student $record) => ! empty($record->supervisorApplication?->loa_path))
                    ->modalHeading(fn (Student $record) => 'LoA - '.$record->name.' ('.$record->nim.')')
                    ->modalContent(function (Student $record): HtmlString {
                        $previewUrl = route('dpm.loa.preview', $record);
                        $downloadUrl = route('dpm.loa.download', $record);
                        $loaPath = $record->supervisorApplication->loa_path;
                        $ext = pathinfo($loaPath, PATHINFO_EXTENSION);
                        $isPdf = strtolower($ext) === 'pdf';

                        if ($isPdf) {
                            $preview = '<iframe src="'.$previewUrl.'" class="w-full rounded-lg border" style="height:70vh;"></iframe>';
                        } else {
                            $preview = '<img src="'.$previewUrl.'" alt="LoA" class="max-w-full max-h-[70vh] mx-auto rounded-lg shadow" />';
                        }

                        return new HtmlString(
                            '<div class="space-y-4">'.
                                $preview.
                                '<div class="flex justify-end">'.
                                    '<a href="'.$downloadUrl.'" '.
                                       'class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-primary-700 transition">'.
                                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>'.
                                        'Download LoA'.
                                    '</a>'.
                                '</div>'.
                            '</div>'
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('5xl'),
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
