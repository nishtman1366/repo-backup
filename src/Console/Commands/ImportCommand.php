<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Nishtman\RepoBackup\Support\RepositoryConfigStore;

class ImportCommand extends Command
{
    protected $signature = 'repo-backup:import {--file=}';

    protected $description = 'Import repository configuration from JSON';

    public function handle(RepositoryConfigStore $store): int
    {
        $file = $this->option('file');
        if ($file === null || ! is_file($file)) {
            $this->components->error('Please provide a valid import file.');

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (! is_array($payload)) {
            $this->components->error('The import file is not valid JSON.');

            return self::FAILURE;
        }

        foreach ($payload as $item) {
            if (! is_array($item)) {
                continue;
            }
            $store->import($item);
        }

        $this->components->info('Repository configuration imported successfully.');

        return self::SUCCESS;
    }
}
