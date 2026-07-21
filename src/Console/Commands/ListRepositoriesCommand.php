<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Models\Repository;
use Symfony\Component\Console\Helper\Table;

class ListRepositoriesCommand extends Command
{
    protected $signature = 'repo-backup:list';

    protected $description = 'List configured repositories';

    public function handle(): int
    {
        $repositories = Repository::query()->orderByDesc('created_at')->get();
        if ($repositories->isEmpty()) {
            $this->components->warn('No repositories configured yet.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($repositories as $repository) {
            $lastHistory = $repository->histories()->latest('started_at')->first();
            $rows[] = [
                $repository->getKey(),
                $repository->title,
                $repository->url,
                implode(is_array($repository->backup_branches) ? $repository->backup_branches : [], ', '),
                $repository->schedule,
                $repository->enabled ? 'Enabled' : 'Disabled',
                $lastHistory?->started_at?->toDateTimeString() ?? '—',
                $lastHistory?->finished_at?->toDateTimeString() ?? '—',
            ];
        }

        $table = new Table($this->output);
        $table->setHeaders(['ID', 'Title', 'Repository URL', 'Branches', 'Schedule', 'Status', 'Last Backup', 'Next Backup']);
        $table->setRows($rows);
        $table->render();

        return self::SUCCESS;
    }
}
