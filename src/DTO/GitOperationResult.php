<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\DTO;

final class GitOperationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly int $exitCode,
        public readonly string $stdout,
        public readonly string $stderr,
        public readonly float $durationSeconds,
        public readonly string $command,
        public readonly ?string $commitHash = null,
        public readonly ?string $commitMessage = null,
    ) {
    }
}
