<?php

namespace App\Filament\Resources\Ppaip;

use App\Models\Internship;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PpaipInternshipResource extends Resource
{
    protected static ?string $model = Internship::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Lowongan Magang';
    protected static ?string $navigationGroup = 'Data';

    protected static ?string $modelLabel = 'Lowongan Magang';

    protected static ?string $pluralModelLabel = 'Lowongan Magang';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'ppaip/internships';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'ppaip';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info Perusahaan')
                ->description('Informasi dasar tentang perusahaan dan posisi magang.')
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->label('Nama Perusahaan')
                        ->placeholder('Contoh: PT. Gojek Tokopedia')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('position')
                        ->label('Posisi / Judul Magang')
                        ->placeholder('Contoh: Software Engineer Intern')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('location')
                        ->label('Lokasi')
                        ->placeholder('Contoh: Jakarta Selatan')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('sistem_kerja')
                        ->label('Sistem Kerja')
                        ->options([
                            'WFO (On-site)' => 'WFO (On-site)',
                            'Hybrid'        => 'Hybrid',
                            'WFH (Remote)'  => 'WFH (Remote)',
                        ])
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Detail Lowongan')
                ->description('Deskripsi, kapasitas, dan batas waktu pendaftaran.')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Umum')
                        ->placeholder('Deskripsi singkat tentang program magang...')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('capacity')
                        ->label('Kapasitas')
                        ->placeholder('Contoh: 3 Posisi')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('duration')
                        ->label('Durasi')
                        ->placeholder('Contoh: 3 Bulan')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('bidang')
                        ->label('Bidang')
                        ->placeholder('Contoh: Software Engineering')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Mulai Magang')
                        ->native(false)
                        ->displayFormat('d M Y'),
                    Forms\Components\TextInput::make('minimum_education')
                        ->label('Pendidikan Minimum')
                        ->placeholder('Contoh: S1 Mahasiswa Aktif (Semester 6 ke atas)')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('deadline')
                        ->label('Deadline Pendaftaran')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Tampilkan di Portal')
                        ->helperText('Nonaktifkan untuk menyembunyikan lowongan dari portal mahasiswa.')
                        ->default(true)
                        ->inline(false),
                ])->columns(2),

            Forms\Components\Section::make('Deskripsi Pekerjaan')
                ->description('Daftar tugas dan tanggung jawab posisi magang.')
                ->schema([
                    Forms\Components\Repeater::make('job_description')
                        ->label('')
                        ->simple(
                            Forms\Components\TextInput::make('item')
                                ->placeholder('Contoh: Mengembangkan fitur baru menggunakan React.js')
                                ->required(),
                        )
                        ->addActionLabel('+ Tambah Deskripsi Pekerjaan')
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(1)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Keahlian Utama')
                ->description('Daftar skill yang dibutuhkan. Ditampilkan sebagai chip pada halaman detail.')
                ->schema([
                    Forms\Components\Repeater::make('skills')
                        ->label('')
                        ->simple(
                            Forms\Components\TextInput::make('item')
                                ->placeholder('Contoh: Figma Specialist')
                                ->required(),
                        )
                        ->addActionLabel('+ Tambah Keahlian')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Persyaratan')
                ->description('Checklist persyaratan tambahan yang harus dipenuhi pelamar.')
                ->schema([
                    Forms\Components\Repeater::make('requirements')
                        ->label('')
                        ->simple(
                            Forms\Components\TextInput::make('item')
                                ->placeholder('Contoh: Memiliki portofolio desain UI/UX')
                                ->required(),
                        )
                        ->addActionLabel('+ Tambah Persyaratan')
                        ->reorderable()
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('position')
                    ->label('Posisi')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->icon('heroicon-m-map-pin'),
                Tables\Columns\TextColumn::make('sistem_kerja')
                    ->label('Sistem Kerja')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'WFO (On-site)' => 'warning',
                        'Hybrid'        => 'info',
                        'WFH (Remote)'  => 'success',
                        default         => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Internship $record): string =>
                        $record->deadline->isPast() ? 'danger' : 'success'
                    ),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Pelamar')
                    ->counts('applications')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (Internship $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (Internship $record) => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Internship $record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Internship $record) => $record->update(['is_active' => !$record->is_active])),
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
            'view'   => \App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\ViewInternship::route('/{record}'),
            'edit'   => \App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\EditInternship::route('/{record}/edit'),
        ];
    }
}
