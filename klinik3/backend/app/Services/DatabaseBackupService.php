<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Dapatkan path direktori backup
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Jalankan pembuatan backup database snapshot
     */
    public function createBackup(): array
    {
        $connection = config('database.default', 'sqlite');
        $timestamp = date('Y-m-d_His');

        if ($connection === 'sqlite') {
            return $this->backupSqlite($timestamp);
        }

        return $this->backupMysql($timestamp);
    }

    /**
     * Backup khusus koneksi SQLite
     */
    protected function backupSqlite(string $timestamp): array
    {
        $dbPath = config('database.connections.sqlite.database');
        if ($dbPath !== ':memory:' && !file_exists($dbPath) && file_exists(database_path($dbPath))) {
            $dbPath = database_path($dbPath);
        }

        $filename = "backup_klinik_sqlite_{$timestamp}.sqlite";
        $targetPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        $backedUp = false;

        // 1. Coba VACUUM INTO jika tidak sedang berada dalam transaksi database
        if (DB::transactionLevel() === 0) {
            try {
                DB::statement("VACUUM INTO \"{$targetPath}\"");
                $backedUp = true;
            } catch (\Throwable $e) {
                $backedUp = false;
            }
        }

        // 2. Jika di dalam transaksi atau VACUUM gagal, salin file jika ada di disk
        if (!$backedUp && $dbPath !== ':memory:' && file_exists($dbPath)) {
            copy($dbPath, $targetPath);
            $backedUp = true;
        }

        // 3. Fallback jika database in-memory (:memory: di testing), clone tabel & data
        if (!$backedUp) {
            $destPdo = new \PDO("sqlite:{$targetPath}");
            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $table) {
                if ($table->sql) {
                    $destPdo->exec($table->sql);
                    $rows = DB::table($table->name)->get();
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        if (!empty($rowArray)) {
                            $cols = array_keys($rowArray);
                            $placeholders = implode(',', array_fill(0, count($cols), '?'));
                            $stmt = $destPdo->prepare("INSERT INTO \"{$table->name}\" (\"" . implode('","', $cols) . "\") VALUES ({$placeholders})");
                            $stmt->execute(array_values($rowArray));
                        }
                    }
                }
            }
            $destPdo = null;
            $backedUp = true;
        }

        $size = file_exists($targetPath) ? filesize($targetPath) : 0;

        return [
            'success' => true,
            'driver' => 'sqlite',
            'filename' => $filename,
            'path' => $targetPath,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Backup koneksi MySQL / MariaDB / TiDB
     */
    protected function backupMysql(string $timestamp): array
    {
        $config = config('database.connections.mysql');
        $filename = "backup_klinik_mysql_{$timestamp}.sql";
        $targetPath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $user = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? 'forge';

        // Coba mysqldump jika tersedia di server
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s 2>/dev/null',
            escapeshellarg($user),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($targetPath)
        );

        $exitCode = 0;
        @exec($command, $output, $exitCode);

        // Jika mysqldump tidak ada / gagal, generate skrip SQL dasar via PHP
        if ($exitCode !== 0 || !file_exists($targetPath) || filesize($targetPath) === 0) {
            $this->generatePhpMysqlDump($targetPath);
        }

        $size = file_exists($targetPath) ? filesize($targetPath) : 0;

        return [
            'success' => true,
            'driver' => 'mysql',
            'filename' => $filename,
            'path' => $targetPath,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
            'created_at' => Carbon::now()->toDateTimeString(),
        ];
    }

    /**
     * Fallback generator SQL dump dari PHP
     */
    protected function generatePhpMysqlDump(string $targetPath): void
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $keyName = "Tables_in_{$dbName}";

        $fp = fopen($targetPath, 'w');
        fwrite($fp, "-- Backup Klinik Gigi Senyum Sehat\n");
        fwrite($fp, "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n");

        foreach ($tables as $t) {
            $tableName = $t->$keyName ?? array_values((array) $t)[0];
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql = $createTable[0]->{'Create Table'} ?? '';
            fwrite($fp, "DROP TABLE IF EXISTS `{$tableName}`;\n{$sql};\n\n");

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    return "'" . addslashes((string) $val) . "'";
                }, (array) $row);

                fwrite($fp, "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n");
            }
            fwrite($fp, "\n");
        }

        fclose($fp);
    }

    /**
     * Download backup terbaru atau buat baru jika belum ada
     */
    public function downloadBackup(): BinaryFileResponse
    {
        $backup = $this->createBackup();
        return response()->download($backup['path'], $backup['filename']);
    }

    /**
     * Daftar riwayat backup yang tersedia di storage/app/backups
     */
    public function listBackups(): array
    {
        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (in_array($file->getExtension(), ['sqlite', 'sql', 'gz', 'zip'])) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'path' => $file->getRealPath(),
                    'size' => $file->getSize(),
                    'size_human' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                    'mtime' => $file->getMTime(),
                ];
            }
        }

        // Urutkan terbaru dulu
        usort($backups, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        return $backups;
    }

    /**
     * Format byte ke satuan yang mudah dibaca manusia
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
