<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_histories', function (Blueprint $table): void {
            if (! Schema::hasColumn('backup_histories', 'duration_seconds')) {
                $table->decimal('duration_seconds', 10, 4)->nullable()->after('finished_at');
            }
            if (! Schema::hasColumn('backup_histories', 'stdout')) {
                $table->text('stdout')->nullable()->after('output');
            }
            if (! Schema::hasColumn('backup_histories', 'stderr')) {
                $table->text('stderr')->nullable()->after('stdout');
            }
            if (! Schema::hasColumn('backup_histories', 'exit_code')) {
                $table->integer('exit_code')->nullable()->after('stderr');
            }
            if (! Schema::hasColumn('backup_histories', 'commit_hash')) {
                $table->string('commit_hash')->nullable()->after('exit_code');
            }
            if (! Schema::hasColumn('backup_histories', 'commit_message')) {
                $table->text('commit_message')->nullable()->after('commit_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('backup_histories', function (Blueprint $table): void {
            $table->dropColumn(['duration_seconds', 'stdout', 'stderr', 'exit_code', 'commit_hash', 'commit_message']);
        });
    }
};
