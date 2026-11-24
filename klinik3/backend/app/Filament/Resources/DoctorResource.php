<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
use App\Filament\Resources\DoctorResource\RelationManagers\SchedulesRelationManager;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Dokter';

    protected static ?string $modelLabel = 'Dokter';

    protected static ?string $pluralModelLabel = 'Daftar Dokter';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->isAdmin() ?? false) && $record->bookings()->count() === 0;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Dokter')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->label('Gelar / Spesialisasi')
                    ->default('Dokter Gigi Umum')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('years_experience')
                    ->label('Pengalaman')
                    ->numeric()
                    ->suffix('tahun')
                    ->required(),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Foto Dokter')
                    ->image()
                    ->disk('public')
                    ->directory('doctors')
                    ->maxSize(2048)
                    ->imageCropAspectRatio('3:4'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn (Doctor $record): ?string => $record->photo_url),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Dokter')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Spesialisasi'),
                Tables\Columns\TextColumn::make('years_experience')
                    ->label('Pengalaman')
                    ->formatStateUsing(fn ($state) => "{$state} tahun"),
                Tables\Columns\TextColumn::make('schedules_count')
                    ->counts('schedules')
                    ->label('Hari Praktik')
                    ->formatStateUsing(fn ($state) => "{$state} hari/pekan"),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Doctor $record) {
                        if ($record->bookings()->exists()) {
                            Notification::make()
                                ->title('Dokter tidak dapat dihapus')
                                ->body('Dokter ini telah memiliki riwayat booking. Silakan nonaktifkan saja status aktifnya.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
