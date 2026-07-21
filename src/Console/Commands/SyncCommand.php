<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Services\BackupManager;

class SyncCommand extends Command
{
    protected $signature = 'repo-backup:sync {--repository=} {--branch=}';

    protected $description = 'Synchronize configured repositories immediately';

    public function handle(BackupManager $backupManager): int
    {
        $repositoryId = $this->option('repository') !== null ? (int) $this->option('repository') : null;
        $branch = $this->option('branch');
        $backupManager->backupAll($repositoryId, $branch);

        return self::SUCCESS;
    }
}
