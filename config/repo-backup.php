<?php

declare(strict_types=1);

return [
    'git_binary' => env('REPO_BACKUP_GIT_BINARY', 'git'),
    'default_backup_path' => env('REPO_BACKUP_DEFAULT_BACKUP_PATH', env('REPO_BACKUP_DEFAULT_PATH', storage_path('repo-backups'))),
    'default_path' => env('REPO_BACKUP_DEFAULT_PATH', env('REPO_BACKUP_DEFAULT_BACKUP_PATH', storage_path('repo-backups'))),
    'timeout' => (int) env('REPO_BACKUP_TIMEOUT', 300),
    'lock_timeout' => (int) env('REPO_BACKUP_LOCK_TIMEOUT', 60),
    'driver' => env('REPO_BACKUP_DRIVER', 'database'),
    'history_retention_days' => (int) env('REPO_BACKUP_HISTORY_RETENTION_DAYS', 90),
    'history_retention_count' => (int) env('REPO_BACKUP_HISTORY_RETENTION_COUNT', 100),
    'default_schedule' => env('REPO_BACKUP_DEFAULT_SCHEDULE', 'daily'),
    'parallel_backups' => (bool) env('REPO_BACKUP_PARALLEL_BACKUPS', false),
    'log_channel' => env('REPO_BACKUP_LOG_CHANNEL', 'repo-backup'),
];
