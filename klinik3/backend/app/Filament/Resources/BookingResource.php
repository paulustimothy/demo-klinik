<?php

namespace App\Filament\Resources;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\Service;
use App\Services\ExportService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Reservasi';

    protected static ?string $modelLabel = 'Reservasi';

    protected static ?string $pluralModelLabel = 'Daftar Reservasi';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pasien & Sumber Reservasi')
                    ->description('Data identitas pasien serta saluran/sumber reservasi masuk')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Booking')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (string $operation): bool => $operation !== 'create'),

                        Forms\Components\TextInput::make('patient_name')
                            ->label('Nama Pasien')
                            ->placeholder('Contoh: Budi Santoso')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('patient_whatsapp')
                            ->label('Nomor WhatsApp')
                            ->placeholder('Contoh: 08123456789 atau 628123456789')
                            ->required()
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Nomor WhatsApp aktif untuk konfirmasi jadwal.'),

                        Forms\Components\Select::make('source')
                            ->label('Sumber Booking')
                            ->options([
                                BookingSource::WHATSAPP->value => 'WhatsApp',
                                BookingSource::PHONE->value => 'Telepon',
                                BookingSource::WALK_IN->value => 'Walk-in (Datang Langsung)',
                                BookingSource::WEBSITE->value => 'Website',
                            ])
                            ->default(BookingSource::WHATSAPP->value)
                            ->required()
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->helperText('Asal reservasi (WhatsApp, Telepon, Walk-in, atau Website)'),

                        Forms\Components\Select::make('status')
                            ->label('Status Awal Booking')
                            ->options([
                                BookingStatus::VERIFIED->value => 'Terverifikasi (Langsung Masuk Jadwal)',
                                BookingStatus::PENDING->value => 'Menunggu Verifikasi (Hold Slot Sementara)',
                            ])
                            ->default(BookingStatus::VERIFIED->value)
                            ->required()
                            ->selectablePlaceholder(false)
                            ->native(false)
                            ->helperText('Untuk booking manual admin, status dapat langsung Terverifikasi.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Layanan, Dokter & Jadwal Praktik')
                    ->description('Pilih layanan klinis, dokter gigi, serta tanggal dan slot jam praktik')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Layanan')
                            ->relationship('service', 'name', fn ($query) => $query->where('is_active', true)->orderBy('sort_order'))
                            ->preload()
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('doctor_id')
                            ->label('Dokter Gigi')
                            ->relationship('doctor', 'name', fn ($query) => $query->where('is_active', true))
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('start_time', null)),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Reservasi')
                            ->required()
                            ->native(false)
                            ->minDate(today())
                            ->default(today())
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('start_time', null)),

                        Forms\Components\Select::make('start_time')
                            ->label('Jam Praktik (Slot Waktu)')
                            ->required()
                            ->native(false)
                            ->live()
                            ->options(function (Forms\Get $get, ?Model $record) {
                                $doctorId = (int) $get('doctor_id');
                                $dateStr = $get('date');

                                if (!$doctorId || !$dateStr) {
                                    return [];
                                }

                                return static::getAvailableSlotsForDoctorDate($doctorId, $dateStr, $record?->id);
                            })
                            ->helperText(function (Forms\Get $get) {
                                $doctorId = (int) $get('doctor_id');
                                $dateStr = $get('date');
                                if (!$doctorId || !$dateStr) {
                                    return 'Pilih Dokter dan Tanggal terlebih dahulu untuk memuat slot waktu yang tersedia.';
                                }

                                $date = Carbon::parse($dateStr);
                                if ($date->dayOfWeekIso === 7) {
                                    return 'Klinik tutup pada hari Minggu.';
                                }

                                $doctor = Doctor::find($doctorId);
                                $schedule = $doctor?->schedules()->where('weekday', $date->dayOfWeekIso)->first();
                                if (!$schedule) {
                                    return "Dokter {$doctor?->name} tidak berpraktik pada hari " . static::getIndonesianDayName($date->dayOfWeekIso) . '.';
                                }

                                return "Jadwal praktik: " . substr($schedule->start_time, 0, 5) . " - " . substr($schedule->end_time, 0, 5) . " WIB.";
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Reservasi / Keluhan Pasien')
                            ->placeholder('Keluhan pasien atau instruksi tambahan...')
                            ->maxLength(200)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $clinic = ClinicSetting::instance();
        $clinicName = $clinic->clinic_name ?? 'Klinik Gigi Senyum Sehat';

        return $table
            ->poll('30s')
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Pasien')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('patient_whatsapp')
                    ->label('WhatsApp'),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof BookingSource) {
                            return $state->label();
                        }
                        return match ($state) {
                            'website' => 'Website',
                            'whatsapp' => 'WhatsApp',
                            'phone' => 'Telepon',
                            'walk_in' => 'Walk-in',
                            default => $state ? ucfirst((string) $state) : 'Website',
                        };
                    })
                    ->color(function ($state) {
                        if ($state instanceof BookingSource) {
                            return $state->color();
                        }
                        return match ($state) {
                            'website' => 'info',
                            'whatsapp' => 'success',
                            'phone' => 'warning',
                            'walk_in' => 'primary',
                            default => 'gray',
                        };
                    })
                    ->icon(function ($state) {
                        if ($state instanceof BookingSource) {
                            return $state->icon();
                        }
                        return match ($state) {
                            'website' => 'heroicon-m-globe-alt',
                            'whatsapp' => 'heroicon-m-chat-bubble-left-ellipsis',
                            'phone' => 'heroicon-m-phone',
                            'walk_in' => 'heroicon-m-user',
                            default => null,
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan'),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Dokter'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . ' WIB'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                    ->color(fn (BookingStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('remaining_hold_minutes')
                    ->label('Sisa Waktu')
                    ->badge()
                    ->color(fn (int $state): string => $state < 30 ? 'danger' : 'warning')
                    ->formatStateUsing(function ($state, Booking $record): string {
                        if ($record->status !== BookingStatus::PENDING) {
                            return '-';
                        }
                        return "{$state} mnt";
                    }),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        BookingSource::WEBSITE->value => 'Website',
                        BookingSource::WHATSAPP->value => 'WhatsApp',
                        BookingSource::PHONE->value => 'Telepon',
                        BookingSource::WALK_IN->value => 'Walk-in',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        BookingStatus::PENDING->value => 'Menunggu verifikasi',
                        BookingStatus::VERIFIED->value => 'Terverifikasi',
                        BookingStatus::COMPLETED->value => 'Selesai',
                        BookingStatus::CANCELLED->value => 'Dibatalkan',
                        BookingStatus::EXPIRED->value => 'Kedaluwarsa',
                        BookingStatus::NO_SHOW->value => 'Tidak Hadir (No-Show)',
                    ]),
                SelectFilter::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // 1. Verifikasi (pending only) - Tombol utama tetap di luar agar 1-klik cepat
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->visible(fn (Booking $record): bool => $record->status === BookingStatus::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Booking')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi reservasi ini? Slot jadwal pasien akan dikonfirmasi permanen.')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->action(function (Booking $record): void {
                        $record->verify();
                        Notification::make()
                            ->title('Reservasi berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),

                // Group aksi lainnya disatukan dalam menu titik tiga (ActionGroup)
                Tables\Actions\ActionGroup::make([
                    // 2. Chat pasien (wa.me)
                    Action::make('chat')
                        ->label('Chat Pasien')
                        ->icon('heroicon-m-chat-bubble-left-ellipsis')
                        ->color('info')
                        ->url(function (Booking $record): string {
                            $clinic = ClinicSetting::instance();
                            $clinicName = $clinic->clinic_name ?? 'Klinik Gigi Senyum Sehat';
                            $tanggal = Carbon::parse($record->date)->translatedFormat('d F Y');
                            $jam = substr($record->start_time, 0, 5) . ' WIB';

                            if ($record->status === BookingStatus::CANCELLED) {
                                $message = "Halo {$record->patient_name}, booking telah dibatalkan. Jadwal Anda di {$clinicName} pada {$tanggal} jam {$jam} telah dibatalkan. Kode: {$record->code}.";
                            } elseif ($record->status === BookingStatus::EXPIRED) {
                                $message = "Halo {$record->patient_name}, booking telah kedaluwarsa. Jadwal Anda di {$clinicName} pada {$tanggal} jam {$jam} telah kedaluwarsa. Kode: {$record->code}.";
                            } elseif ($record->status === BookingStatus::NO_SHOW) {
                                $message = "Halo {$record->patient_name}, kami mencatat Anda belum sempat hadir pada jadwal di {$clinicName} pada {$tanggal} jam {$jam}. Ingin mengatur ulang jadwal reservasi Anda? Kode: {$record->code}.";
                            } else {
                                $message = "Halo {$record->patient_name}, jadwal Anda di {$clinicName} pada {$tanggal} jam {$jam} sudah dikonfirmasi. Kode: {$record->code}.";
                            }

                            $phone = preg_replace('/[^\d]/', '', (string) $record->patient_whatsapp);
                            if (str_starts_with($phone, '08')) {
                                $phone = '62' . substr($phone, 1);
                            }

                            return "https://wa.me/{$phone}?text=" . urlencode($message);
                        })
                        ->openUrlInNewTab(),

                    // 3. Edit Reservasi
                    Tables\Actions\EditAction::make()
                        ->label('Edit Reservasi')
                        ->icon('heroicon-m-pencil-square')
                        ->color('gray'),

                    // 4. Selesai (verified only)
                    Action::make('complete')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->visible(fn (Booking $record): bool => $record->status === BookingStatus::VERIFIED)
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Selesai')
                        ->modalDescription('Apakah kunjungan pasien ini sudah selesai?')
                        ->modalSubmitActionLabel('Ya, Selesai')
                        ->action(function (Booking $record): void {
                            $record->complete();
                            Notification::make()
                                ->title('Reservasi ditandai selesai')
                                ->success()
                                ->send();
                        }),

                    // 5. Batalkan (pending or verified)
                    Action::make('cancel')
                        ->label('Batalkan Booking')
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->visible(fn (Booking $record): bool => in_array($record->status, [BookingStatus::PENDING, BookingStatus::VERIFIED]))
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Booking')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan reservasi ini? Slot jadwal akan kembali tersedia untuk pasien lain.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->action(function (Booking $record): void {
                            $record->cancel();
                            Notification::make()
                                ->title('Reservasi berhasil dibatalkan')
                                ->warning()
                                ->send();
                        }),

                    // 6. Tidak Hadir / No-Show (verified only)
                    Action::make('no_show')
                        ->label('Tandai No-Show')
                        ->icon('heroicon-m-user-minus')
                        ->color('danger')
                        ->visible(fn (Booking $record): bool => $record->status === BookingStatus::VERIFIED)
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Pasien Tidak Hadir (No-Show)')
                        ->modalDescription('Apakah pasien ini tidak hadir pada jadwal yang telah dikonfirmasi? Status akan diubah menjadi No-Show dan slot jadwal akan dibebaskan.')
                        ->modalSubmitActionLabel('Ya, Tandai No-Show')
                        ->action(function (Booking $record): void {
                            $record->markNoShow();
                            Notification::make()
                                ->title('Reservasi ditandai Tidak Hadir (No-Show)')
                                ->warning()
                                ->send();
                        }),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Opsi Tindakan')
                ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('exportExcel')
                        ->label('Export ke Excel (.xlsx)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($livewire) {
                            $records = $livewire->getFilteredTableQuery()->with(['service', 'doctor'])->get();
                            return app(ExportService::class)->exportBookings($records, 'xlsx');
                        }),
                    Tables\Actions\Action::make('exportCsv')
                        ->label('Export ke CSV (.csv)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function ($livewire) {
                            $records = $livewire->getFilteredTableQuery()->with(['service', 'doctor'])->get();
                            return app(ExportService::class)->exportBookings($records, 'csv');
                        }),
                ])
                ->label('Export Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportBulkExcel')
                    ->label('Export Terpilih (Excel)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $records->loadMissing(['service', 'doctor']);
                        return app(ExportService::class)->exportBookings($records, 'xlsx');
                    }),
                Tables\Actions\BulkAction::make('exportBulkCsv')
                    ->label('Export Terpilih (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $records->loadMissing(['service', 'doctor']);
                        return app(ExportService::class)->exportBookings($records, 'csv');
                    }),
            ]);
    }

    public static function getIndonesianDayName(int $weekday): string
    {
        return match ($weekday) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => '',
        };
    }

    /**
     * Hitung slot waktu yang tersedia untuk dokter dan tanggal tertentu
     */
    public static function getAvailableSlotsForDoctorDate(int $doctorId, string $dateStr, ?int $ignoreBookingId = null): array
    {
        try {
            $date = Carbon::parse($dateStr);
        } catch (\Throwable $e) {
            return [];
        }

        // Klinik tutup hari Minggu (ISO weekday 7)
        if ($date->dayOfWeekIso === 7) {
            return [];
        }

        $doctor = Doctor::where('is_active', true)->find($doctorId);
        if (!$doctor) {
            return [];
        }

        $weekday = $date->dayOfWeekIso; // 1 = Monday ... 6 = Saturday
        $schedule = $doctor->schedules()->where('weekday', $weekday)->first();
        if (!$schedule) {
            return [];
        }

        $settings = ClinicSetting::instance();
        $slotMinutes = $settings->slot_minutes ?: 60;

        $startTime = Carbon::createFromTimeString($schedule->start_time);
        $endTime = Carbon::createFromTimeString($schedule->end_time);

        // Ambil booking aktif untuk dokter & tanggal ini
        $activeBookings = Booking::where('doctor_id', $doctorId)
            ->where('date', $date->toDateString())
            ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
            ->whereIn('status', [BookingStatus::PENDING, BookingStatus::VERIFIED])
            ->get()
            ->filter(function ($booking) {
                // Jika pending tetapi sudah expired, anggap tidak aktif
                if ($booking->status === BookingStatus::PENDING && $booking->expires_at && $booking->expires_at->isPast()) {
                    return false;
                }
                return true;
            })
            ->pluck('start_time')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        $slots = [];
        $current = $startTime->copy();

        while ($current->copy()->addMinutes($slotMinutes)->lte($endTime)) {
            $timeStr = $current->format('H:i');
            $isBooked = in_array($timeStr, $activeBookings, true);

            if (!$isBooked) {
                $slots[$timeStr] = "{$timeStr} WIB";
            }

            $current->addMinutes($slotMinutes);
        }

        // Jika dalam mode edit dan record saat ini memiliki start_time, sertakan dalam pilihan
        if ($ignoreBookingId) {
            $currentBooking = Booking::find($ignoreBookingId);
            if ($currentBooking && $currentBooking->date->toDateString() === $date->toDateString() && $currentBooking->doctor_id === $doctorId) {
                $curTime = substr($currentBooking->start_time, 0, 5);
                if (!isset($slots[$curTime])) {
                    $slots[$curTime] = "{$curTime} WIB (Slot Saat Ini)";
                    ksort($slots);
                }
            }
        }

        return $slots;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
