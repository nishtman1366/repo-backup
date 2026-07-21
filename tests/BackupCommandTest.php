<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Tests;

use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\DTO\GitOperationResult;
use Nishtman\RepoBackup\DTO\RepositoryValidationResult;
use Nishtman\RepoBackup\Models\Repository;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class BackupCommandTest extends TestCase
{
    #[Test]
    public function it_executes_the_backup_command_and_records_history(): void
    {
        $repository = Repository::create([
            'title' => 'Example Repo',
            'url' => 'https://github.com/example/repo.git',
            'path' => '/tmp/repo-backups/example-repo',
            'branches' => ['main'],
            'backup_branches' => ['main'],
            'schedule' => 'daily',
            'enabled' => true,
        ]);

        $this->app->instance(GitManagerInterface::class, new class implements GitManagerInterface {
            public function backupRepository(Repository $repository, string $branch): GitOperationResult
            {
                return new GitOperationResult(true, 0, 'ok', '', 0.1, 'git pull');
            }

            public function validateRepository(Repository $repository): RepositoryValidationResult
            {
                return new RepositoryValidationResult(true, 'ok', [], ['main']);
            }

            public function discoverRemoteBranches(string $repositoryUrl): array
            {
                return ['main'];
            }

            public function getGitVersion(): string
            {
                return 'git version 2.0';
            }

            public function isAvailable(): bool
            {
                return true;
            }
        });

        $this->artisan('repo-backup:backup', ['--repository' => $repository->getKey()])->assertSuccessful();

        $this->assertDatabaseHas('backup_histories', [
            'repository_id' => $repository->getKey(),
            'branch' => 'main',
            'status' => 'completed',
        ]);
    }

    #[Test]
    public function it_records_failed_git_operations_in_history(): void
    {
        $repository = Repository::create([
            'title' => 'Failed Repo',
            'url' => 'https://github.com/example/failed.git',
            'path' => '/tmp/repo-backups/failed-repo',
            'branches' => ['main'],
            'backup_branches' => ['main'],
            'schedule' => 'daily',
            'enabled' => true,
        ]);

        $this->app->instance(GitManagerInterface::class, new class implements GitManagerInterface {
            public function backupRepository(Repository $repository, string $branch): GitOperationResult
            {
                throw new RuntimeException('Unable to backup repository');
            }

            public function validateRepository(Repository $repository): RepositoryValidationResult
            {
                return new RepositoryValidationResult(true, 'ok', [], ['main']);
            }

            public function discoverRemoteBranches(string $repositoryUrl): array
            {
                return ['main'];
            }

            public function getGitVersion(): string
            {
                return 'git version 2.0';
            }

            public function isAvailable(): bool
            {
                return true;
            }
        });

        $this->artisan('repo-backup:backup', ['--repository' => $repository->getKey()])->assertSuccessful();

        $this->assertDatabaseHas('backup_histories', [
            'repository_id' => $repository->getKey(),
            'branch' => 'main',
            'status' => 'failed',
        ]);
    }
}
