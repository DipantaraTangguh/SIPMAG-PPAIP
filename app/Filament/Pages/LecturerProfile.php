<?php

namespace App\Filament\Pages;

use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
class LecturerProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profil Saya';
    protected static ?string $navigationGroup = 'Akun';
    protected static ?int $navigationSort = 99;
    protected static ?string $slug = 'lecturer-profile';
    protected static ?string $title = 'Profil Saya';
    protected static string $view = 'filament.pages.lecturer-profile';

    public ?array $data = [];
    public static function canAccess(): bool
    {
        $role = auth()->user()?->role;
        return in_array($role, ['kaprodi', 'dpm']);
    }
    public function mount(): void
    {
        $lecturer = $this->getLecturer();

        $this->form->fill([
            'lecturer_name'  => $lecturer->lecturer_name ?? '',
            'nidn'           => $lecturer->nidn ?? '',
            'contact'        => $lecturer->contact ?? '',
            'study_program'  => $lecturer->study_program ?? '',
            'signature_path' => $lecturer->signature_path,
        ]);
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Dosen')
                    ->description('Informasi profil Anda. Hubungi PPAIP jika ada data yang perlu diubah.')
                    ->schema([
                        Forms\Components\TextInput::make('lecturer_name')
                            ->label('Nama Lengkap')
                            ->disabled(),
                        Forms\Components\TextInput::make('nidn')
                            ->label('NIDN')
                            ->disabled(),
                        Forms\Components\TextInput::make('contact')
                            ->label('Email / Kontak')
                            ->disabled(),
                        Forms\Components\TextInput::make('study_program')
                            ->label('Program Studi')
                            ->disabled()
                            ->placeholder('—'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tanda Tangan Digital')
                    ->description('Unggah gambar tanda tangan Anda. Gambar ini akan digunakan pada dokumen resmi seperti Surat Keterangan Form Magang 01.')
                    ->schema([
                        Forms\Components\FileUpload::make('signature_path')
                            ->label('File Tanda Tangan')
                            ->helperText('Gunakan gambar PNG dengan latar transparan untuk hasil terbaik. Maks. 2 MB.')
                            ->image()
                            ->directory('signatures')
                            ->disk('public')
                            ->acceptedFileTypes(['image/png', 'image/jpeg'])
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }
    public function save(): void
    {
        $data = $this->form->getState();
        $lecturer = $this->getLecturer();

        $lecturer->update([
            'signature_path' => $data['signature_path'],
        ]);

        Notification::make()
            ->title('Tanda tangan berhasil disimpan')
            ->success()
            ->send();
    }
    private function getLecturer(): Lecturer
    {
        return auth()->user()->lecturer;
    }
}
