<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('repository_id')->constrained('repositories')->cascadeOnDelete();
            $table->string('branch');
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('duration_seconds', 10, 4)->nullable();
            $table->text('output')->nullable();
            $table->text('stdout')->nullable();
            $table->text('stderr')->nullable();
            $table->text('error')->nullable();
            $table->integer('exit_code')->nullable();
            $table->string('commit_hash')->nullable();
            $table->text('commit_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_histories');
    }
};
