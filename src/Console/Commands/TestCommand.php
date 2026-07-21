<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Services\BackupManager;

class TestCommand extends Command
{
    protected $signature = 'repo-backup:test {--repository=} {--branch=}';

    protected $description = 'Run a simulated backup without modifying repositories';

    public function handle(BackupManager $backupManager): int
    {
        $repositoryId = $this->option('repository') !== null ? (int) $this->option('repository') : null;
        $branch = $this->option('branch');

        $results = $backupManager->backupAll($repositoryId, $branch, true);
        $this->components->info('Dry run completed.');

        if ($results === []) {
            return self::SUCCESS;
        }

        foreach ($results as $result) {
            $this->line(sprintf('%s [%s] => %s', $result->repository, $result->branch, $result->status->value));
        }

        return self::SUCCESS;
    }
}
