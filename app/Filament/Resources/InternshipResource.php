<?php

namespace App\Filament\Resources;

use App\Models\Internship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternshipResource extends Resource
{
    protected static ?string $model = Internship::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Lowongan Magang';
    protected static ?string $navigationGroup = 'Portal';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Perusahaan')->schema([
                Forms\Components\TextInput::make('company_name')->label('Nama Perusahaan')->required(),
                Forms\Components\TextInput::make('position')->label('Posisi')->required(),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->required()->rows(3),
                Forms\Components\TextInput::make('vacancy_details')->label('Detail Lowongan')->placeholder('2 Posisi · Hybrid · 3 Bulan'),
            ])->columns(2),

            Forms\Components\Section::make('Persyaratan')->schema([
                Forms\Components\TagsInput::make('job_description')->label('Deskripsi Pekerjaan (per bullet)'),
                Forms\Components\TextInput::make('minimum_education')->label('Pendidikan Minimal'),
                Forms\Components\Select::make('sistem_kerja')->label('Sistem Kerja')
                    ->options(['WFO (On-site)' => 'WFO', 'WFH' => 'WFH', 'Hybrid' => 'Hybrid']),
                Forms\Components\TextInput::make('location')->label('Lokasi'),
            ])->columns(2),

            Forms\Components\Section::make('Status')->schema([
                Forms\Components\DatePicker::make('deadline')->label('Deadline')->required(),
                Forms\Components\Toggle::make('is_active')->label('Aktif di Portal')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label('Posisi')->searchable(),
                Tables\Columns\TextColumn::make('location')->label('Lokasi'),
                Tables\Columns\TextColumn::make('sistem_kerja')->label('Tipe'),
                Tables\Columns\TextColumn::make('deadline')->label('Deadline')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Pelamar')
                    ->counts('applications'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\InternshipResource\Pages\ListInternships::route('/'),
            'create' => \App\Filament\Resources\InternshipResource\Pages\CreateInternship::route('/create'),
            'edit'   => \App\Filament\Resources\InternshipResource\Pages\EditInternship::route('/{record}/edit'),
        ];
    }
}
