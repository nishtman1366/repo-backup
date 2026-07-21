<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup;

use Illuminate\Console\Scheduling\Schedule;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Services\Schedule\RepositoryScheduleResolver;

final class RepoBackup
{
    public static function schedule(?Schedule $schedule = null): Schedule
    {
        $schedule ??= app(Schedule::class);
        $schedule->command('repo-backup:backup')->daily();

        $resolver = app(RepositoryScheduleResolver::class);

        foreach (Repository::enabled()->get() as $repository) {
            $schedule->command(sprintf('repo-backup:backup --repository=%d', $repository->getKey()))
                ->cron($resolver->resolveForRepository($repository));
        }

        return $schedule;
    }
}
