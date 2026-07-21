<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Support;

use Illuminate\Support\Arr;
use Nishtman\RepoBackup\Models\Repository;

final class RepositoryConfigStore
{
    /**
     * @param iterable<Repository> $repositories
     * @return array<int, array<string, mixed>>
     */
    public function export(iterable $repositories): array
    {
        $payload = [];

        foreach ($repositories as $repository) {
            $payload[] = [
                'title' => $repository->title,
                'url' => $repository->url,
                'path' => $repository->path,
                'branches' => $repository->branches ?? [],
                'backup_branches' => $repository->backup_branches ?? [],
                'schedule' => $repository->schedule,
                'enabled' => (bool) $repository->enabled,
            ];
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function import(array $data): Repository
    {
        $repository = new Repository();
        $repository->fill([
            'title' => Arr::get($data, 'title', 'Imported repository'),
            'url' => Arr::get($data, 'url', ''),
            'path' => Arr::get($data, 'path', ''),
            'branches' => Arr::get($data, 'branches', []),
            'backup_branches' => Arr::get($data, 'backup_branches', []),
            'schedule' => Arr::get($data, 'schedule', config('repo-backup.default_schedule', 'daily')),
            'enabled' => (bool) Arr::get($data, 'enabled', true),
        ]);
        $repository->save();

        return $repository;
    }
}
