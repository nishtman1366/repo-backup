<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Models\BackupHistory;
use Symfony\Component\Console\Helper\Table;

class HistoryCommand extends Command
{
    protected $signature = 'repo-backup:history';

    protected $description = 'Show backup history';

    public function handle(): int
    {
        $histories = BackupHistory::query()->with('repository')->latest('started_at')->get();
        if ($histories->isEmpty()) {
            $this->components->warn('No backup history yet.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($histories as $history) {
            $rows[] = [
                $history->repository?->title ?? 'Unknown',
                $history->branch,
                $history->started_at?->toDateTimeString() ?? '—',
                $history->finished_at?->toDateTimeString() ?? '—',
                $history->status,
                $history->duration_seconds !== null ? number_format($history->duration_seconds, 2) . 's' : '—',
                $history->exit_code ?? '—',
                $history->error ?: '—',
            ];
        }

        $table = new Table($this->output);
        $table->setHeaders(['Repository', 'Branch', 'Started At', 'Finished At', 'Status', 'Duration', 'Exit Code', 'Error']);
        $table->setRows($rows);
        $table->render();

        return self::SUCCESS;
    }
}
