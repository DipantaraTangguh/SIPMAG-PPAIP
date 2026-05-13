<?php

namespace App\Filament\Resources\Ppaip;

use App\Models\Internship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PpaipInternshipResource extends Resource
{
    protected static ?string $model = Internship::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Lowongan Magang';
    protected static ?string $navigationGroup = 'Data';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'ppaip/internships';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'ppaip';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Perusahaan')->schema([
                Forms\Components\TextInput::make('company_name')->label('Nama Perusahaan')->required(),
                Forms\Components\TextInput::make('position')->label('Posisi')->required(),
                Forms\Components\TextInput::make('location')->label('Lokasi')->required(),
                Forms\Components\Select::make('sistem_kerja')
                    ->label('Sistem Kerja')
                    ->options([
                        'WFO (On-site)' => 'WFO (On-site)',
                        'Hybrid'        => 'Hybrid',
                        'WFH (Remote)'  => 'WFH (Remote)',
                    ]),
            ])->columns(2),

            Forms\Components\Section::make('Detail Lowongan')->schema([
                Forms\Components\Textarea::make('description')->label('Deskripsi')->required(),
                Forms\Components\TextInput::make('vacancy_details')->label('Detail Lowongan'),
                Forms\Components\Textarea::make('job_description')->label('Deskripsi Pekerjaan (JSON)'),
                Forms\Components\TextInput::make('minimum_education')->label('Pendidikan Min.'),
                Forms\Components\DatePicker::make('deadline')->label('Deadline')->required(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('position')->label('Posisi')->searchable(),
                Tables\Columns\TextColumn::make('location')->label('Lokasi'),
                Tables\Columns\TextColumn::make('sistem_kerja')->label('Sistem Kerja'),
                Tables\Columns\TextColumn::make('deadline')->label('Deadline')->date('d M Y')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\ListInternships::route('/'),
            'create' => \App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\CreateInternship::route('/create'),
            'edit'   => \App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\EditInternship::route('/{record}/edit'),
        ];
    }
}
