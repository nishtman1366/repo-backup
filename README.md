# repo-backup

A production-ready Laravel package for automatically backing up Git repositories through Artisan commands, Laravel scheduling, and rich CLI UX.

## Features

- Manage repositories with interactive Artisan commands
- Validate Git remotes and destination paths before saving
- Clone, fetch, checkout, and pull repositories using Symfony Process
- Record backup history with timings, exit codes, and output
- Prevent concurrent backups with Laravel cache locks
- Export and import repository configurations as JSON
- Diagnose package health with `repo-backup:doctor`

## Installation

```bash
composer require nishtman/repo-backup
```

## Publishing configuration

```bash
php artisan vendor:publish --tag=repo-backup-config
php artisan vendor:publish --tag=repo-backup-migrations
```

## Running migrations

```bash
php artisan migrate
```

## Artisan commands

- `php artisan repo-backup:add`
- `php artisan repo-backup:list`
- `php artisan repo-backup:remove {id}`
- `php artisan repo-backup:history`
- `php artisan repo-backup:backup`
- `php artisan repo-backup:schedule`
- `php artisan repo-backup:doctor`
- `php artisan repo-backup:test`
- `php artisan repo-backup:export`
- `php artisan repo-backup:import --file=path/to/file.json`
- `php artisan repo-backup:sync`

## Scheduling

```php
use Illuminate\Support\Facades\Schedule;
use Nishtman\RepoBackup\RepoBackup;

Schedule::command('repo-backup:backup')->daily();
RepoBackup::schedule();
```

## Import and export

```bash
php artisan repo-backup:export --file=repo-backups.json
php artisan repo-backup:import --file=repo-backups.json
```

## FAQ

### Does the package require a Laravel app?
Yes, it is designed for Laravel applications and packages.

### Can I run backups manually?
Yes, use `php artisan repo-backup:backup` or `php artisan repo-backup:sync`.

## Contributing

Contributions are welcome. Please open an issue or submit a pull request.
