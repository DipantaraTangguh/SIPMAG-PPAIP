<?php

namespace App\Filament\Resources;

use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LecturerResource extends Resource
{
    protected static ?string $model = Lecturer::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Dosen';
    protected static ?string $navigationGroup = 'Akademik';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nidn')->label('NIDN')->required()->maxLength(20),
            Forms\Components\TextInput::make('lecturer_name')->label('Nama Dosen')->required(),
            Forms\Components\TextInput::make('contact')->label('Kontak'),
            Forms\Components\Select::make('study_program')
                ->label('Program Studi')
                ->options(['Sistem Informasi' => 'Sistem Informasi', 'Informatika' => 'Informatika'])
                ->required(),
            Forms\Components\Select::make('user_id')
                ->label('Akun User')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nidn')->label('NIDN')->searchable(),
                Tables\Columns\TextColumn::make('lecturer_name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('study_program')->label('Prodi'),
                Tables\Columns\TextColumn::make('user.role')->label('Role')->badge(),
                Tables\Columns\TextColumn::make('supervised_students_count')
                    ->label('Mahasiswa Bimbingan')
                    ->counts('supervisedStudents'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\LecturerResource\Pages\ListLecturers::route('/'),
            'create' => \App\Filament\Resources\LecturerResource\Pages\CreateLecturer::route('/create'),
            'edit'   => \App\Filament\Resources\LecturerResource\Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}
