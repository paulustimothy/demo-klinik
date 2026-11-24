<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected static ?string $title = 'Edit Reservasi';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['patient_whatsapp'])) {
            $rawPhone = preg_replace('/[^\d]/', '', (string) $data['patient_whatsapp']);
            if (str_starts_with($rawPhone, '08')) {
                $rawPhone = '62' . substr($rawPhone, 1);
            } elseif (str_starts_with($rawPhone, '8')) {
                $rawPhone = '62' . $rawPhone;
            }
            $data['patient_whatsapp'] = $rawPhone;
        }

        if (isset($data['start_time'])) {
            $data['start_time'] = substr($data['start_time'], 0, 5);
        }

        if (isset($data['doctor_id'], $data['date'], $data['start_time'])) {
            $data['slot_key'] = Booking::generateSlotKey((int) $data['doctor_id'], $data['date'], $data['start_time']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
