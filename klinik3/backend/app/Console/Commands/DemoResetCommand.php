<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DemoResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     */
    protected $description = 'Reset data demo ke kondisi awal seeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Mereset database demo...');

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->info(Artisan::output());

        Artisan::call('cache:clear');

        $this->info('Data demo berhasil direset ke kondisi awal.');
        return Command::SUCCESS;
    }
}

