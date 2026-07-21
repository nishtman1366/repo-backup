<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Enums;

enum BackupStatus: string
{
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
