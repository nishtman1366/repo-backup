<?php

declare(strict_types=1);

namespace Nishtman\RepoBackup\Support;

use Composer\InstalledVersions;

final class PackageVersion
{
    public static function get(): string
    {
        return (string) InstalledVersions::getPrettyVersion('nishtman/repo-backup') ?: 'dev';
    }
}
