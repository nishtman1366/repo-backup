<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Services;

use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\DTO\GitOperationResult;
use Nishtman\RepoBackup\DTO\RepositoryValidationResult;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Services\Git\GitManager as GitService;
use Nishtman\RepoBackup\Support\RepositoryPathResolver;

final class GitManager implements GitManagerInterface
{
    private readonly GitService $service;

    public function __construct(string $gitBinary = 'git', int $timeout = 300)
    {
        $this->service = new GitService(new RepositoryPathResolver(), $gitBinary, $timeout);
    }

    public function backupRepository(Repository $repository, string $branch): GitOperationResult
    {
        return $this->service->backupRepository($repository, $branch);
    }

    public function validateRepository(Repository $repository): RepositoryValidationResult
    {
        return $this->service->validateRepository($repository);
    }

    public function discoverRemoteBranches(string $repositoryUrl): array
    {
        return $this->service->discoverRemoteBranches($repositoryUrl);
    }

    public function getGitVersion(): string
    {
        return $this->service->getGitVersion();
    }

    public function isAvailable(): bool
    {
        return $this->service->isAvailable();
    }
}
