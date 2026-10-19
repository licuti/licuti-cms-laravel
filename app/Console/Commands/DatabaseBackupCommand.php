<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup 
                            {--keep=3 : Maximum number of latest backups to keep} 
                            {--push : Automatically commit and push to git after backup}
                            {--message= : Commit message when pushing to git}';

    protected $description = 'Backup database licuti-cms-laravel, retain 3 latest backups, and optionally push to git.';

    public function handle(): int
    {
        $dbConfig = config('database.connections.mysql');
        $database = $dbConfig['database'] ?? 'licuti-cms-laravel';
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';

        $mysqldump = $this->findMysqldump();
        if (!$mysqldump) {
            $this->error('mysqldump binary not found. Please ensure MySQL is installed and in PATH or Laragon directory.');
            return self::FAILURE;
        }

        $date = now()->format('Y-m-d');
        $databaseDir = database_path();
        $targetFile = $databaseDir . DIRECTORY_SEPARATOR . "backup-{$database}-{$date}.sql";

        $this->info("Creating backup for [{$database}] to [{$targetFile}]...");

        $command = [
            $mysqldump,
            "-h{$host}",
            "-P{$port}",
            "-u{$username}",
            '--default-character-set=utf8mb4',
            '--routines',
            '--triggers',
            "--result-file={$targetFile}",
            $database,
        ];

        if (!empty($password)) {
            $command[] = "-p{$password}";
        }

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Backup failed: ' . $process->getErrorOutput());
            return self::FAILURE;
        }

        $this->info("Backup created successfully: " . basename($targetFile));

        // Cleanup old backups, keeping $keep latest
        $keep = (int) $this->option('keep');
        $this->pruneOldBackups($databaseDir, "backup-{$database}-*.sql", $keep);

        if ($this->option('push')) {
            $this->info('Running git sync...');
            return $this->pushToGit($date);
        }

        return self::SUCCESS;
    }

    private function pruneOldBackups(string $dir, string $pattern, int $keep): void
    {
        $files = glob($dir . DIRECTORY_SEPARATOR . $pattern);
        if (!$files) {
            return;
        }

        rsort($files); // Sort descending (newest first by filename YYYY-MM-DD)

        if (count($files) > $keep) {
            $filesToDelete = array_slice($files, $keep);
            foreach ($filesToDelete as $file) {
                $filename = basename($file);
                $this->warn("Removing older backup: {$filename}");

                // Try git rm first
                $gitRm = new Process(['git', 'rm', '-f', $file], base_path());
                $gitRm->run();

                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }

        $remaining = array_slice($files, 0, $keep);
        $this->info("Retained " . count($remaining) . " latest backups:");
        foreach ($remaining as $f) {
            $this->line(" - " . basename($f));
        }
    }

    private function pushToGit(string $date): int
    {
        $add = new Process(['git', 'add', '-A'], base_path());
        $add->mustRun();

        $message = $this->option('message') ?: "chore: backup database ({$date}) and sync changes";
        $commit = new Process(['git', 'commit', '-m', $message], base_path());
        $commit->run();

        if ($commit->isSuccessful()) {
            $this->info('Committed changes: ' . $message);
        } else {
            $this->line('No changes to commit or commit skipped.');
        }

        $push = new Process(['git', 'push', 'origin', 'main'], base_path());
        $push->setTimeout(120);
        $push->run();

        if ($push->isSuccessful()) {
            $this->info('Successfully pushed to git origin/main.');
            return self::SUCCESS;
        }

        $this->error('Failed to push to git: ' . $push->getErrorOutput());
        return self::FAILURE;
    }

    private function findMysqldump(): ?string
    {
        if (env('MYSQLDUMP_PATH') && file_exists(env('MYSQLDUMP_PATH'))) {
            return env('MYSQLDUMP_PATH');
        }

        // Check PATH
        $which = new Process(PHP_OS_FAMILY === 'Windows' ? ['where', 'mysqldump'] : ['which', 'mysqldump']);
        $which->run();
        if ($which->isSuccessful()) {
            $lines = explode("\n", trim($which->getOutput()));
            if (!empty($lines[0]) && file_exists(trim($lines[0]))) {
                return trim($lines[0]);
            }
        }

        // Check Laragon defaults on Windows
        $laragonDirs = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe');
        if (!empty($laragonDirs) && file_exists($laragonDirs[0])) {
            return $laragonDirs[0];
        }

        return null;
    }
}
