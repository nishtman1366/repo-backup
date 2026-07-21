<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Tests;

use Nishtman\RepoBackup\Models\BackupHistory;
use Nishtman\RepoBackup\Models\Repository;
use PHPUnit\Framework\Attributes\Test;

class RepositoryModelTest extends TestCase
{
    #[Test]
    public function it_creates_a_repository_with_expected_attributes(): void
    {
        $repository = Repository::create([
            'title' => 'Example Repo',
            'url' => 'https://github.com/example/repo.git',
            'path' => '/tmp/repo-backups/example-repo',
            'branches' => ['main', 'develop'],
            'backup_branches' => ['main'],
            'schedule' => 'daily',
            'enabled' => true,
        ]);

        $this->assertSame('Example Repo', $repository->title);
        $this->assertSame(['main', 'develop'], $repository->branches);
    }

    #[Test]
    public function it_creates_backup_history_for_a_repository(): void
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

        $history = BackupHistory::create([
            'repository_id' => $repository->getKey(),
            'branch' => 'main',
            'status' => 'completed',
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        $this->assertTrue($repository->histories()->whereKey($history->getKey())->exists());
    }
}
