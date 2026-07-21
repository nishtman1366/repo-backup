<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Tests;

use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Services\Git\GitManager;
use Nishtman\RepoBackup\Support\RepositoryPathResolver;
use PHPUnit\Framework\Attributes\Test;

class GitManagerTest extends TestCase
{
    #[Test]
    public function it_marks_unreachable_repositories_as_invalid(): void
    {
        $manager = new GitManager(new RepositoryPathResolver(), 'git', 10);
        $repository = new Repository([
            'title' => 'Example',
            'url' => 'https://example.invalid/repo.git',
            'path' => '/tmp/repo-backups/example',
            'branches' => [],
            'backup_branches' => [],
            'schedule' => 'daily',
            'enabled' => true,
        ]);

        $result = $manager->validateRepository($repository);
        $this->assertFalse($result->success);
    }
}
