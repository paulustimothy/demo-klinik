<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpirePendingBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:expire';

    /**
     * The console command description.
     */
    protected $description = 'Expire pending bookings whose hold time has passed and release their slots';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredBookings = Booking::where('status', BookingStatus::PENDING)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredBookings as $booking) {
            $booking->expire();
            $count++;
        }

        $this->info("Berhasil kedaluwarsa {$count} booking yang melewati batas hold.");
        return Command::SUCCESS;
    }
}

