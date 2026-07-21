<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup;

use Illuminate\Support\ServiceProvider;
use Nishtman\RepoBackup\Console\Commands\AddRepositoryCommand;
use Nishtman\RepoBackup\Console\Commands\BackupCommand;
use Nishtman\RepoBackup\Console\Commands\DoctorCommand;
use Nishtman\RepoBackup\Console\Commands\ExportCommand;
use Nishtman\RepoBackup\Console\Commands\HistoryCommand;
use Nishtman\RepoBackup\Console\Commands\ImportCommand;
use Nishtman\RepoBackup\Console\Commands\ListRepositoriesCommand;
use Nishtman\RepoBackup\Console\Commands\RemoveRepositoryCommand;
use Nishtman\RepoBackup\Console\Commands\ScheduleCommand;
use Nishtman\RepoBackup\Console\Commands\SyncCommand;
use Nishtman\RepoBackup\Console\Commands\TestCommand;
use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\Services\BackupManager;
use Nishtman\RepoBackup\Services\GitManager;
use Nishtman\RepoBackup\Services\Schedule\RepositoryScheduleResolver;
use Nishtman\RepoBackup\Support\RepositoryConfigStore;
use Nishtman\RepoBackup\Support\RepositoryPathResolver;

class RepoBackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/repo-backup.php', 'repo-backup');

        $this->app->singleton(RepositoryPathResolver::class);
        $this->app->singleton(RepositoryConfigStore::class);
        $this->app->singleton(RepositoryScheduleResolver::class);

        $this->app->bind(GitManagerInterface::class, function ($app): GitManagerInterface {
            $config = $app['config']->get('repo-backup', []);

            return new GitManager(
                gitBinary: (string) ($config['git_binary'] ?? 'git'),
                timeout: (int) ($config['timeout'] ?? 300),
            );
        });

        $this->app->singleton(BackupManager::class, function ($app): BackupManager {
            return new BackupManager($app->make(GitManagerInterface::class));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/repo-backup.php' => config_path('repo-backup.php'),
            ], 'repo-backup-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'repo-backup-migrations');

            $this->commands([
                AddRepositoryCommand::class,
                ListRepositoriesCommand::class,
                RemoveRepositoryCommand::class,
                HistoryCommand::class,
                BackupCommand::class,
                ScheduleCommand::class,
                DoctorCommand::class,
                TestCommand::class,
                ExportCommand::class,
                ImportCommand::class,
                SyncCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
