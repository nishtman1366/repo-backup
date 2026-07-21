<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->string('path');
            $table->json('branches')->nullable();
            $table->json('backup_branches')->nullable();
            $table->string('schedule')->default('daily');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
