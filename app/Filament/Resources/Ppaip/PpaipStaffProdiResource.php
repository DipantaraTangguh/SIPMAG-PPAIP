<?php

namespace App\Filament\Resources\Ppaip;

use App\Models\User;
use App\Support\StudyProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Akun Staff Prodi: petugas yang memproses berkas atas nama Kaprodi.
 *
 * Kewenangannya identik dengan Kaprodi, jadi role-nya memang 'kaprodi'.
 * Yang membedakan: staff TIDAK punya baris di tabel dosen. Itu bukan
 * kebetulan -- staff adalah tenaga kependidikan tanpa NIDN, sementara tabel
 * dosen memasok nama dan NIDN ke surat resmi serta mengisi daftar calon DPM
 * dan dosen penguji. Menaruh staff di sana berarti menaruh NIDN karangan di
 * jalur dokumen resmi sekaligus membuat mereka bisa dipilih sebagai penguji.
 *
 * Karena itu program studi staff disimpan di users.study_program, dan
 * ketiadaan baris dosen itulah yang dipakai untuk mengenali mereka.
 */
class PpaipStaffProdiResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Staff Prodi';

    protected static ?string $navigationGroup = 'Data';

    protected static ?string $modelLabel = 'Staff Prodi';

    protected static ?string $pluralModelLabel = 'Staff Prodi';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'ppaip/staff-prodi';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'ppaip';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'kaprodi')
            ->whereDoesntHave('lecturer');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Akun Staff Prodi')
                ->description('Staff memproses berkas atas nama Kaprodi. Surat Keterangan tetap tercetak dengan nama dan NIDN Kaprodi program studi yang dipilih.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Staff')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email (dipakai untuk login)')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\Select::make('study_program')
                        ->label('Program Studi')
                        ->options(StudyProgram::options())
                        ->required()
                        ->helperText('Menentukan mahasiswa mana yang bisa diproses staff ini.'),

                    Forms\Components\TextInput::make('password')
                        ->label('Kata Sandi')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->helperText('Kosongkan saat mengubah data bila kata sandi tidak diganti.')
                        ->maxLength(255),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('study_program')
                    ->label('Prodi')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('study_program')
                    ->label('Prodi')
                    ->options(StudyProgram::options()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => PpaipStaffProdiResource\Pages\ListStaffProdi::route('/'),
            'create' => PpaipStaffProdiResource\Pages\CreateStaffProdi::route('/create'),
            'edit' => PpaipStaffProdiResource\Pages\EditStaffProdi::route('/{record}/edit'),
        ];
    }
}
