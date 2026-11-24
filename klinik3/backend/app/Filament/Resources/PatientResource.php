<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pasien';

    protected static ?string $modelLabel = 'Pasien';

    protected static ?string $pluralModelLabel = 'Daftar Pasien';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pasien')
                    ->searchable()
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('whatsapp')
                    ->label('Nomor WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->counts('bookings')
                    ->label('Total Kunjungan')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_visit')
                    ->label('Kunjungan Terakhir')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Sejak')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // 1. Modal Riwayat Kunjungan
                    Action::make('view_history')
                        ->label('Riwayat Kunjungan')
                        ->icon('heroicon-m-calendar-days')
                        ->color('primary')
                        ->modalHeading(fn (Patient $record) => "Riwayat Kunjungan: {$record->name}")
                        ->modalDescription('Daftar seluruh riwayat booking dan jadwal kunjungan pasien di klinik.')
                        ->modalWidth('4xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup')
                        ->modalContent(fn (Patient $record) => view('filament.resources.patient-visits-modal', [
                            'patient' => $record,
                            'bookings' => $record->bookings()->with(['doctor', 'service'])->latest('date')->get(),
                        ])),

                    // 2. Chat WhatsApp langsung
                    Action::make('chat')
                        ->label('Chat WA Pasien')
                        ->icon('heroicon-m-chat-bubble-left-ellipsis')
                        ->color('success')
                        ->url(function (Patient $record): string {
                            $phone = preg_replace('/[^\d]/', '', (string) $record->whatsapp);
                            if (str_starts_with($phone, '08')) {
                                $phone = '62' . substr($phone, 1);
                            }
                            $msg = "Halo {$record->name}, salam dari Klinik Gigi Senyum Sehat.";
                            return "https://wa.me/{$phone}?text=" . urlencode($msg);
                        })
                        ->openUrlInNewTab(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Menu Aksi'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
        ];
    }
}

