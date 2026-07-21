<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    protected $table = 'repositories';

    protected $fillable = [
        'title',
        'url',
        'path',
        'branches',
        'backup_branches',
        'schedule',
        'enabled',
    ];

    protected $casts = [
        'branches' => 'array',
        'backup_branches' => 'array',
        'enabled' => 'boolean',
    ];

    public function histories(): HasMany
    {
        return $this->hasMany(BackupHistory::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
