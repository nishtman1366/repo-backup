<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Services\Git;

use Illuminate\Support\Str;
use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\DTO\GitOperationResult;
use Nishtman\RepoBackup\DTO\RepositoryValidationResult;
use Nishtman\RepoBackup\Exceptions\GitException;
use Nishtman\RepoBackup\Models\Repository;
use Nishtman\RepoBackup\Support\RepositoryPathResolver;
use Symfony\Component\Process\Process;

final class GitManager implements GitManagerInterface
{
    public function __construct(
        private readonly RepositoryPathResolver $pathResolver,
        private readonly string $gitBinary = 'git',
        private readonly int $timeout = 300,
    ) {
    }

    public function backupRepository(Repository $repository, string $branch): GitOperationResult
    {
        $path = $this->pathResolver->resolve($repository);
        $this->ensureDirectory($path);

        if (! is_dir($path . '/.git')) {
            $cloneResult = $this->runProcess(dirname($path), [$this->gitBinary, 'clone', '--branch', $branch, $repository->url, $path]);
            if (! $cloneResult->success) {
                throw new GitException($cloneResult->stderr !== '' ? $cloneResult->stderr : $cloneResult->stdout);
            }
        }

        $fetchResult = $this->runProcess($path, [$this->gitBinary, 'fetch', 'origin', '--prune']);
        if (! $fetchResult->success) {
            throw new GitException($fetchResult->stderr !== '' ? $fetchResult->stderr : $fetchResult->stdout);
        }

        $checkoutResult = $this->runProcess($path, [$this->gitBinary, 'checkout', $branch]);
        if (! $checkoutResult->success) {
            throw new GitException($checkoutResult->stderr !== '' ? $checkoutResult->stderr : $checkoutResult->stdout);
        }

        $pullResult = $this->runProcess($path, [$this->gitBinary, 'pull', 'origin', $branch]);
        if (! $pullResult->success) {
            throw new GitException($pullResult->stderr !== '' ? $pullResult->stderr : $pullResult->stdout);
        }

        $commitHash = $this->runProcess($path, [$this->gitBinary, 'rev-parse', 'HEAD']);
        $commitMessage = $this->runProcess($path, [$this->gitBinary, 'log', '-1', '--pretty=%B']);

        return new GitOperationResult(
            success: $pullResult->success,
            exitCode: $pullResult->exitCode,
            stdout: $pullResult->stdout,
            stderr: $pullResult->stderr,
            durationSeconds: $pullResult->durationSeconds,
            command: $pullResult->command,
            commitHash: $commitHash->success ? trim($commitHash->stdout) : null,
            commitMessage: $commitMessage->success ? trim($commitMessage->stdout) : null,
        );
    }

    public function validateRepository(Repository $repository): RepositoryValidationResult
    {
        if (! $this->isAvailable()) {
            return new RepositoryValidationResult(false, 'Git executable is not available.', ['Git executable was not found.']);
        }

        $path = $this->pathResolver->resolve($repository);
        $directory = dirname($path);

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return new RepositoryValidationResult(false, 'Destination path could not be created.', ['Destination path could not be created.']);
        }

        if (! is_writable($directory)) {
            return new RepositoryValidationResult(false, 'Destination path is not writable.', ['Destination path is not writable.']);
        }

        $branches = $this->discoverRemoteBranches($repository->url);
        if ($branches === []) {
            return new RepositoryValidationResult(false, 'No remote branches could be discovered.', ['No remote branches could be discovered.']);
        }

        return new RepositoryValidationResult(true, 'Repository configuration is valid.', [], $branches);
    }

    public function discoverRemoteBranches(string $repositoryUrl): array
    {
        $result = $this->runProcess(sys_get_temp_dir(), [$this->gitBinary, 'ls-remote', '--heads', $repositoryUrl]);
        if (! $result->success) {
            return [];
        }

        $branches = [];
        foreach (explode(PHP_EOL, trim($result->stdout)) as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (is_array($parts) && count($parts) >= 2) {
                $branchName = Str::afterLast($parts[1], '/');
                $branches[] = $branchName;
            }
        }

        return array_values(array_unique($branches));
    }

    public function getGitVersion(): string
    {
        $result = $this->runProcess(sys_get_temp_dir(), [$this->gitBinary, '--version']);

        return $result->success ? trim($result->stdout) : '';
    }

    public function isAvailable(): bool
    {
        return $this->getGitVersion() !== '';
    }

    private function ensureDirectory(string $path): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new GitException('Destination path could not be created.');
        }
    }

    private function runProcess(string $workingDirectory, array $command): GitOperationResult
    {
        $process = new Process($command, $workingDirectory, null, null, $this->timeout);
        $start = microtime(true);
        $process->run();
        $duration = microtime(true) - $start;

        return new GitOperationResult(
            success: $process->isSuccessful(),
            exitCode: $process->getExitCode(),
            stdout: $process->getOutput(),
            stderr: $process->getErrorOutput(),
            durationSeconds: (float) round($duration, 4),
            command: implode(' ', array_map(static fn (string $argument): string => escapeshellarg($argument), $command)),
        );
    }
}
