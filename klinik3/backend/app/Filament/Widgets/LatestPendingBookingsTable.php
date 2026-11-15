<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPendingBookingsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '5 Booking Terbaru Menunggu Verifikasi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->where('status', BookingStatus::PENDING)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('patient_name')
                    ->label('Pasien')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_whatsapp')
                    ->label('WhatsApp'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Layanan'),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Dokter'),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . ' WIB'),
                Tables\Columns\TextColumn::make('remaining_hold_minutes')
                    ->label('Sisa Waktu Hold')
                    ->badge()
                    ->color(fn (int $state): string => $state < 30 ? 'danger' : 'warning')
                    ->formatStateUsing(fn (int $state): string => "{$state} menit"),
            ])
            ->paginated(false);
    }
}

