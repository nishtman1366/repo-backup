<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Services\Schedule;

use Nishtman\RepoBackup\Models\Repository;

final class RepositoryScheduleResolver
{
    public function resolveForRepository(Repository $repository): string
    {
        return $this->resolve((string) ($repository->schedule ?: config('repo-backup.default_schedule', 'daily')));
    }

    public function resolve(string $schedule): string
    {
        return match (strtolower(trim($schedule))) {
            'hourly' => '0 * * * *',
            'daily' => '0 0 * * *',
            'weekly' => '0 0 * * 0',
            'monthly' => '0 0 1 * *',
            'everythirtyminutes' => '*/30 * * * *',
            'every30minutes' => '*/30 * * * *',
            default => $schedule,
        };
    }
}
