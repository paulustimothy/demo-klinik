<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat snapshot backup database klinik secara aman (SQLite/MySQL)';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info('Memulai proses backup database klinik...');

        try {
            $result = $backupService->createBackup();

            $this->info("Backup berhasil dibuat!");
            $this->line("Driver   : " . $result['driver']);
            $this->line("File     : " . $result['filename']);
            $this->line("Ukuran   : " . $result['size_human']);
            $this->line("Lokasi   : " . $result['path']);
            $this->line("Waktu    : " . $result['created_at']);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal membuat backup: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

