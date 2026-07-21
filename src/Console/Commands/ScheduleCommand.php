<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Models\Repository;
use Symfony\Component\Console\Helper\Table;

class ScheduleCommand extends Command
{
    protected $signature = 'repo-backup:schedule';

    protected $description = 'Display configured schedules';

    public function handle(): int
    {
        $repositories = Repository::query()->orderBy('title')->get();
        if ($repositories->isEmpty()) {
            $this->components->warn('No repositories configured yet.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($repositories as $repository) {
            $rows[] = [$repository->title, $repository->schedule, $repository->enabled ? 'Enabled' : 'Disabled'];
        }

        $table = new Table($this->output);
        $table->setHeaders(['Repository', 'Schedule', 'Status']);
        $table->setRows($rows);
        $table->render();

        return self::SUCCESS;
    }
}
