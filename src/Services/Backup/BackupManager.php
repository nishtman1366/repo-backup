<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Services\Backup;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Nishtman\RepoBackup\Contracts\GitManagerInterface;
use Nishtman\RepoBackup\DTO\BackupExecutionResult;
use Nishtman\RepoBackup\Enums\BackupStatus;
use Nishtman\RepoBackup\Events\BackupFailed;
use Nishtman\RepoBackup\Events\BackupFinished;
use Nishtman\RepoBackup\Events\BackupStarted;
use Nishtman\RepoBackup\Exceptions\BackupFailedException;
use Nishtman\RepoBackup\Models\BackupHistory;
use Nishtman\RepoBackup\Models\Repository;
use Throwable;

class BackupManager
{
    public function __construct(private readonly GitManagerInterface $gitManager)
    {
    }

    /**
     * @return array<int, BackupExecutionResult>
     */
    public function backupAll(?int $repositoryId = null, ?string $branch = null, bool $dryRun = false): array
    {
        $lock = Cache::lock('repo-backup:backup', (int) config('repo-backup.lock_timeout', 60));
        if (! $lock->get()) {
            return [new BackupExecutionResult('all', 'all', BackupStatus::SKIPPED, 'A backup run is already in progress.')];
        }

        $query = Repository::query();
        if ($repositoryId !== null) {
            $query->whereKey($repositoryId);
        } else {
            $query->enabled();
        }

        $repositories = $query->get();
        $results = [];

        foreach ($repositories as $repository) {
            $results = [...$results, ...$this->backupRepository($repository, $branch, $dryRun)];
        }

        $lock->release();

        return $results;
    }

    /**
     * @return array<int, BackupExecutionResult>
     */
    public function backupRepository(Repository $repository, ?string $branch = null, bool $dryRun = false): array
    {
        $branches = $this->resolveBranches($repository, $branch);
        $results = [];

        foreach ($branches as $branchName) {
            $history = BackupHistory::create([
                'repository_id' => $repository->getKey(),
                'branch' => $branchName,
                'status' => BackupStatus::RUNNING->value,
                'started_at' => now(),
            ]);

            Event::dispatch(new BackupStarted($repository, $branchName));

            try {
                if ($dryRun) {
                    $history->update([
                        'status' => BackupStatus::COMPLETED->value,
                        'finished_at' => now(),
                        'duration_seconds' => 0.0,
                        'output' => 'Dry run complete.',
                        'stdout' => 'Dry run complete.',
                        'stderr' => '',
                        'exit_code' => 0,
                    ]);

                    $result = new BackupExecutionResult(
                        repository: $repository->title,
                        branch: $branchName,
                        status: BackupStatus::COMPLETED,
                        exitCode: 0,
                        duration: 0.0,
                        stdout: 'Dry run complete.'
                    );
                } else {
                    $operation = $this->gitManager->backupRepository($repository, $branchName);
                    $history->update([
                        'status' => $operation->success ? BackupStatus::COMPLETED->value : BackupStatus::FAILED->value,
                        'finished_at' => now(),
                        'duration_seconds' => $operation->durationSeconds,
                        'output' => trim($operation->stdout . PHP_EOL . $operation->stderr),
                        'stdout' => $operation->stdout,
                        'stderr' => $operation->stderr,
                        'exit_code' => $operation->exitCode,
                        'commit_hash' => $operation->commitHash,
                        'commit_message' => $operation->commitMessage,
                    ]);

                    if (! $operation->success) {
                        throw new BackupFailedException($operation->stderr ?: $operation->stdout);
                    }

                    $result = new BackupExecutionResult(
                        repository: $repository->title,
                        branch: $branchName,
                        status: BackupStatus::COMPLETED,
                        exitCode: $operation->exitCode,
                        duration: $operation->durationSeconds,
                        stdout: $operation->stdout,
                        stderr: $operation->stderr,
                        commitHash: $operation->commitHash,
                        commitMessage: $operation->commitMessage,
                    );
                }

                Event::dispatch(new BackupFinished($repository, $branchName));
                $results[] = $result;
            } catch (Throwable $exception) {
                $history->update([
                    'status' => BackupStatus::FAILED->value,
                    'finished_at' => now(),
                    'duration_seconds' => 0.0,
                    'output' => $exception->getMessage(),
                    'error' => $exception->getMessage(),
                    'stderr' => $exception->getMessage(),
                    'exit_code' => 1,
                ]);

                Event::dispatch(new BackupFailed($repository, $branchName, $exception->getMessage()));
                Log::channel(config('repo-backup.log_channel', 'repo-backup'))->error('Backup failed', [
                    'repository' => $repository->title,
                    'branch' => $branchName,
                    'exception' => $exception->getMessage(),
                ]);

                $results[] = new BackupExecutionResult(
                    repository: $repository->title,
                    branch: $branchName,
                    status: BackupStatus::FAILED,
                    error: $exception->getMessage(),
                    exitCode: 1,
                );
            }
        }

        $this->cleanupHistory();

        return $results;
    }

    /**
     * @return array<int, string>
     */
    private function resolveBranches(Repository $repository, ?string $branch): array
    {
        if ($branch !== null) {
            return [$branch];
        }

        if (is_array($repository->backup_branches) && count($repository->backup_branches) > 0) {
            return array_values($repository->backup_branches);
        }

        if (is_array($repository->branches) && count($repository->branches) > 0) {
            return array_values($repository->branches);
        }

        return [];
    }

    private function cleanupHistory(): void
    {
        $days = (int) config('repo-backup.history_retention_days', 90);
        $count = (int) config('repo-backup.history_retention_count', 100);

        if ($days > 0) {
            BackupHistory::query()->where('created_at', '<', now()->subDays($days))->delete();
        }

        if ($count > 0) {
            $histories = BackupHistory::query()->select('id')->orderByDesc('created_at')->get();
            $ids = $histories->slice($count)->pluck('id')->all();
            BackupHistory::query()->whereIn('id', $ids)->delete();
        }
    }
}
