<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Services;

use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\DTO\BackupExecutionResult;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Services\Backup\BackupManager as BackupService;

final class BackupManager
{
    private readonly BackupService $service;

    public function __construct(GitManagerInterface $gitManager)
    {
        $this->service = new BackupService($gitManager);
    }

    /**
     * @return array<int, BackupExecutionResult>
     */
    public function backupAll(?int $repositoryId = null, ?string $branch = null, bool $dryRun = false): array
    {
        return $this->service->backupAll($repositoryId, $branch, $dryRun);
    }

    /**
     * @return array<int, BackupExecutionResult>
     */
    public function backupRepository(Repository $repository, ?string $branch = null, bool $dryRun = false): array
    {
        return $this->service->backupRepository($repository, $branch, $dryRun);
    }
}
