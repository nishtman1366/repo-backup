<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Services\BackupManager;
use Symfony\Component\Console\Helper\Table;

class BackupCommand extends Command
{
    protected $signature = 'repo-backup:backup {--repository=} {--branch=}';

    protected $description = 'Run repository backups';

    public function handle(BackupManager $backupManager): int
    {
        $repositoryId = $this->option('repository') !== null ? (int) $this->option('repository') : null;
        $branch = $this->option('branch');

        $results = $backupManager->backupAll($repositoryId, $branch);
        $rows = [];
        foreach ($results as $result) {
            $rows[] = [
                $result->repository,
                $result->branch,
                $result->status->value,
                $result->error ?? '—',
            ];
        }

        if ($rows === []) {
            $this->components->warn('No repositories matched the selection.');

            return self::SUCCESS;
        }

        $table = new Table($this->output);
        $table->setHeaders(['Repository', 'Branch', 'Status', 'Error']);
        $table->setRows($rows);
        $table->render();

        $failedCount = count(array_filter($results, static fn ($result): bool => $result->status->value === 'failed'));
        if ($failedCount > 0) {
            $this->components->warn(sprintf('%d backup(s) failed.', $failedCount));

            return self::SUCCESS;
        }

        $this->components->info('Backups completed successfully.');

        return self::SUCCESS;
    }
}
