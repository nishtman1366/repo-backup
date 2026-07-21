<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\DTO;

use Nishtman\RepoBackup\Enums\BackupStatus;

final class BackupExecutionResult
{
    public function __construct(
        public readonly string $repository,
        public readonly string $branch,
        public readonly BackupStatus $status,
        public readonly ?string $error = null,
        public readonly ?int $exitCode = null,
        public readonly ?string $commitHash = null,
        public readonly ?string $commitMessage = null,
        public readonly ?float $duration = null,
        public readonly ?string $stdout = null,
        public readonly ?string $stderr = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'repository' => $this->repository,
            'branch' => $this->branch,
            'status' => $this->status->value,
            'error' => $this->error,
            'exit_code' => $this->exitCode,
            'commit_hash' => $this->commitHash,
            'commit_message' => $this->commitMessage,
            'duration' => $this->duration,
            'stdout' => $this->stdout,
            'stderr' => $this->stderr,
        ];
    }
}
