<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use App\Models\DoctorSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Jadwal Praktik Mingguan';

    protected static ?string $modelLabel = 'Jadwal Praktik';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('weekday')
                    ->label('Hari Praktik')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                    ])
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Jam Mulai')
                    ->seconds(false)
                    ->default('09:00')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('Jam Selesai')
                    ->seconds(false)
                    ->default('19:00')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('weekday')
            ->defaultSort('weekday', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('weekday')
                    ->label('Hari')
                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        default => 'Minggu',
                    })
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . ' WIB'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 5) . ' WIB'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Jadwal'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
