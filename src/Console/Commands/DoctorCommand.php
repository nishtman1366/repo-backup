<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\Support\PackageVersion;
use Symfony\Component\Console\Helper\Table;

class DoctorCommand extends Command
{
    protected $signature = 'repo-backup:doctor';

    protected $description = 'Check the package installation health';

    public function handle(GitManagerInterface $gitManager): int
    {
        $rows = [];
        $issues = [];

        $gitInstalled = $gitManager->isAvailable();
        $rows[] = ['Git installed', $gitInstalled ? 'Yes' : 'No', $gitInstalled ? $gitManager->getGitVersion() : 'Not available'];
        if (! $gitInstalled) {
            $issues[] = 'Git is not available.';
        }

        $storagePath = storage_path();
        $storageWritable = is_writable($storagePath);
        $rows[] = ['Storage writable', $storageWritable ? 'Yes' : 'No', $storagePath];
        if (! $storageWritable) {
            $issues[] = 'Storage path is not writable.';
        }

        $rows[] = ['Config valid', is_array(config('repo-backup')) ? 'Yes' : 'No', ''];
        $rows[] = ['Database connection', $this->databaseConnectionHealthy() ? 'Yes' : 'No', ''];
        $rows[] = ['Scheduler configured', class_exists(\Illuminate\Console\Scheduling\Schedule::class) ? 'Yes' : 'No', ''];
        $rows[] = ['Disk free space', $this->humanSize(disk_free_space(base_path())), ''];
        $rows[] = ['Package version', PackageVersion::get(), InstalledVersions::getReference('nishtman/repo-backup') ?: ''];

        $table = new Table($this->output);
        $table->setHeaders(['Check', 'Status', 'Details']);
        $table->setRows($rows);
        $table->render();

        if ($issues !== []) {
            $this->components->warn('Please address the issues above before using the package.');

            return self::FAILURE;
        }

        $this->components->info('Everything looks healthy.');

        return self::SUCCESS;
    }

    private function databaseConnectionHealthy(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function humanSize(float|int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = $bytes;
        $i = 0;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        return sprintf('%.2f %s', $value, $units[$i]);
    }
}
