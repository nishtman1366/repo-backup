<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\Events\RepositoryAdded;
use Nishtman\RepoBackup\Models\Repository;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class AddRepositoryCommand extends Command
{
    protected $signature = 'repo-backup:add';

    protected $description = 'Add a repository configuration with automated validation';

    public function handle(GitManagerInterface $gitManager): int
    {
        $title = text('Repository title');
        $url = text('Git repository URL');

        if ($title === '' || $url === '') {
            $this->components->error('Repository title and URL are required.');

            return self::FAILURE;
        }

        $this->components->info('Validating repository and discovering branches...');
        $repository = new Repository([
            'title' => $title,
            'url' => $url,
            'path' => '',
            'branches' => [],
            'backup_branches' => [],
            'schedule' => config('repo-backup.default_schedule', 'daily'),
            'enabled' => true,
        ]);

        $validation = $gitManager->validateRepository($repository);
        if (! $validation->success) {
            $this->components->error($validation->message);

            return self::FAILURE;
        }

        $branches = $validation->branches;
        $selectedBranches = multiselect('Backup branches', $branches, default: $branches[0] ?? null);
        $path = text('Local backup path', default: $this->defaultPath($title));
        $schedule = select('Backup schedule', ['hourly', 'daily', 'weekly', 'monthly', 'everyThirtyMinutes', 'custom'], default: 'daily');
        $scheduleValue = $schedule !== 'custom' ? $schedule : text('Custom schedule expression');
        $enabled = confirm('Enable repository?', default: true);

        $repository->fill([
            'path' => $path,
            'branches' => $branches,
            'backup_branches' => $selectedBranches,
            'schedule' => $scheduleValue,
            'enabled' => $enabled,
        ]);
        $repository->save();

        Event::dispatch(new RepositoryAdded($repository));
        $this->components->info(sprintf('Repository "%s" added successfully.', $repository->title));

        return self::SUCCESS;
    }

    private function defaultPath(string $title): string
    {
        return rtrim((string) config('repo-backup.default_backup_path', storage_path('repo-backups')), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Str::slug($title);
    }
}
