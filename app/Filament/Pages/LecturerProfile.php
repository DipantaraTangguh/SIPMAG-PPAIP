<?php

namespace App\Filament\Pages;

use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

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
        $role = Auth::user()?->role;
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
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }
    public function save(): void
    {
        $data = $this->form->getState();
        $lecturer = $this->getLecturer();

        $lecturer->update([]);

        Notification::make()
            ->title('Tanda tangan berhasil disimpan')
            ->success()
            ->send();
    }
    private function getLecturer(): Lecturer
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->lecturer;
    }
}
