<?php

namespace App\Filament\Resources\Ppaip;

use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpaipLecturerResource extends Resource
{
    protected static ?string $model = Lecturer::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Dosen';
    protected static ?string $navigationGroup = 'Data';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'ppaip/lecturers';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'ppaip';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Dosen')->schema([
                Forms\Components\TextInput::make('nidn')->label('NIDN')->required(),
                Forms\Components\TextInput::make('lecturer_name')->label('Nama Dosen')->required(),
                Forms\Components\TextInput::make('contact')->label('Kontak')->email(),
                Forms\Components\Select::make('study_program')
                    ->label('Program Studi')
                    ->options([
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Informatika'      => 'Informatika',
                    ])
                    ->nullable(),
                Forms\Components\Select::make('user_id')
                    ->label('User Account')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nidn')->label('NIDN')->searchable(),
                Tables\Columns\TextColumn::make('lecturer_name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('contact')->label('Kontak'),
                Tables\Columns\TextColumn::make('study_program')->label('Prodi')->placeholder('—'),
                Tables\Columns\TextColumn::make('user.role')->label('Role')->badge(),
                Tables\Columns\TextColumn::make('supervisedStudents_count')
                    ->label('Bimbingan')
                    ->counts('supervisedStudents'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\Ppaip\PpaipLecturerResource\Pages\ListLecturers::route('/'),
            'create' => \App\Filament\Resources\Ppaip\PpaipLecturerResource\Pages\CreateLecturer::route('/create'),
            'edit'   => \App\Filament\Resources\Ppaip\PpaipLecturerResource\Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}
