<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qb_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['pull', 'push']);
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('families_processed')->default(0);
            $table->unsignedInteger('payments_processed')->default(0);
            $table->unsignedInteger('conflicts_found')->default(0);
            $table->json('errors')->nullable();
            $table->foreignId('triggered_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qb_sync_logs');
    }
};
