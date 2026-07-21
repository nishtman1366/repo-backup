<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Nishtman\RepoBackup\Events\RepositoryRemoved;
use Nishtman\RepoBackup\Models\Repository;
use function Laravel\Prompts\confirm;

class RemoveRepositoryCommand extends Command
{
    protected $signature = 'repo-backup:remove {id}';

    protected $description = 'Remove a repository configuration';

    public function handle(): int
    {
        $repository = Repository::find($this->argument('id'));
        if ($repository === null) {
            $this->components->error('Repository not found.');

            return self::FAILURE;
        }

        if (! confirm('Delete this repository configuration?')) {
            $this->components->info('Removal cancelled.');

            return self::SUCCESS;
        }

        Event::dispatch(new RepositoryRemoved($repository));
        $repository->delete();
        $this->components->info('Repository removed successfully.');

        return self::SUCCESS;
    }
}
