<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Support\RepositoryConfigStore;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ExportCommand extends Command
{
    protected $signature = 'repo-backup:export {--file=}';

    protected $description = 'Export repository configuration as JSON';

    public function handle(RepositoryConfigStore $store): int
    {
        $payload = $store->export(Repository::query()->get());
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($this->option('file') !== null) {
            file_put_contents($this->option('file'), $json);
            $this->components->info(sprintf('Exported %d repositories to %s.', count($payload), $this->option('file')));

            return self::SUCCESS;
        }

        $this->output->writeln($json);

        return self::SUCCESS;
    }
}
