<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupHistory extends Model
{
    protected $table = 'backup_histories';

    protected $fillable = [
        'repository_id',
        'branch',
        'status',
        'started_at',
        'finished_at',
        'duration_seconds',
        'output',
        'stdout',
        'stderr',
        'error',
        'exit_code',
        'commit_hash',
        'commit_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'float',
        'exit_code' => 'integer',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }
}
