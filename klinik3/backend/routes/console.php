<?php

use Illuminate\Support\Facades\Schedule;

// Expire pending booking yang melewati batas hold setiap menit
Schedule::command('bookings:expire')->everyMinute();

// Reset demo database setiap malam pukul 03:00 WIB
Schedule::command('demo:reset')->dailyAt('03:00');
