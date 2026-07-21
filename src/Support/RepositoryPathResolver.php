<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Support;

use Nishtman\RepoBackup\Models\Repository;

final class RepositoryPathResolver
{
    public function resolve(Repository $repository): string
    {
        if ($repository->path !== '' && $repository->path !== null) {
            return $repository->path;
        }

        $basePath = (string) config('repo-backup.default_backup_path', storage_path('repo-backups'));

        return rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $repository->getKey();
    }
}
