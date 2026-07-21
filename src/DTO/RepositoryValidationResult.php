<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\DTO;

final class RepositoryValidationResult
{
    /**
     * @param array<int, string> $errors
     * @param array<int, string> $branches
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly array $errors = [],
        public readonly array $branches = [],
    ) {
    }
}
