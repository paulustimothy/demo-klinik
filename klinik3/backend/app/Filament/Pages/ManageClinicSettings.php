<?php

namespace App\Filament\Pages;

use App\Models\ClinicSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageClinicSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Klinik';

    protected static ?string $title = 'Pengaturan Klinik';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-clinic-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = ClinicSetting::instance();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Umum Klinik')
                    ->description('Data identitas klinik yang ditampilkan di frontend dan API.')
                    ->schema([
                        Forms\Components\TextInput::make('clinic_name')
                            ->label('Nama Klinik')
                            ->required(),
                        Forms\Components\TextInput::make('tagline')
                            ->label('Tagline / Slogan')
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat Lengkap')
                            ->required(),
                        Forms\Components\TextInput::make('hours_text')
                            ->label('Teks Jam Operasional')
                            ->required(),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp Klinik')
                            ->helperText('Format 628xxxxxxxxxx tanpa tanda +')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Kontak')
                            ->email()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Aturan Reservasi & Slot')
                    ->description('Durasi slot reservasi dan masa penahanan booking sebelum verifikasi.')
                    ->schema([
                        Forms\Components\TextInput::make('hold_minutes')
                            ->label('Durasi Hold (Menit)')
                            ->helperText('Batas waktu booking berstatus pending sebelum kedaluwarsa otomatis (default 120 menit).')
                            ->numeric()
                            ->required()
                            ->suffix('menit'),
                        Forms\Components\TextInput::make('slot_minutes')
                            ->label('Durasi Slot (Menit)')
                            ->helperText('Interval waktu antar slot booking (default 60 menit).')
                            ->numeric()
                            ->required()
                            ->suffix('menit'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = ClinicSetting::instance();
        $setting->update($data);

        Notification::make()
            ->title('Pengaturan klinik berhasil disimpan')
            ->success()
            ->send();
    }
}
