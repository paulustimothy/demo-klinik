<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\ClinicSetting;
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
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Booking')
                    ->disabled(),
                Forms\Components\TextInput::make('patient_name')
                    ->label('Nama Pasien')
                    ->disabled(),
                Forms\Components\TextInput::make('patient_whatsapp')
                    ->label('Nomor WhatsApp')
                    ->disabled(),
                Forms\Components\Select::make('service_id')
                    ->label('Layanan')
                    ->relationship('service', 'name')
                    ->disabled(),
                Forms\Components\Select::make('doctor_id')
                    ->label('Dokter')
                    ->relationship('doctor', 'name')
                    ->disabled(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->disabled(),
                Forms\Components\TextInput::make('start_time')
                    ->label('Jam')
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan Pasien')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $clinic = ClinicSetting::instance();
        $clinicName = $clinic->clinic_name ?? 'Klinik Gigi Senyum Sehat';

        return $table
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
                // 1. Verifikasi (pending only)
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

                // 3. Batalkan (pending or verified)
                Action::make('cancel')
                    ->label('Batalkan')
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

                // 4. Selesai (verified only)
                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-m-check-badge')
                    ->color('gray')
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

                // 5. Tidak Hadir / No-Show (verified only)
                Action::make('no_show')
                    ->label('No-Show')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
        ];
    }
}
