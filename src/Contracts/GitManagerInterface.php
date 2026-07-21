<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Contracts;

use Nishtman\RepoBackup\DTO\GitOperationResult;
use Nishtman\RepoBackup\DTO\RepositoryValidationResult;
use Nishtman\RepoBackup\Models\Repository;

interface GitManagerInterface
{
    public function backupRepository(Repository $repository, string $branch): GitOperationResult;

    public function validateRepository(Repository $repository): RepositoryValidationResult;

    public function discoverRemoteBranches(string $repositoryUrl): array;

    public function getGitVersion(): string;

    public function isAvailable(): bool;
}
