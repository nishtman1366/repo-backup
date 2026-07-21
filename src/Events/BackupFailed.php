<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Events;

use Nishtman\RepoBackup\Models\Repository;

final class BackupFailed
{
    public function __construct(public readonly Repository $repository, public readonly string $branch, public readonly string $message)
    {
    }
}
