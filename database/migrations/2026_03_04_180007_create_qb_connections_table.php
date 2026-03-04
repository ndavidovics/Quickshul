<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qb_connections', function (Blueprint $table) {
            $table->id();
            $table->string('realm_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('access_token_expires_at');
            $table->timestamp('refresh_token_expires_at');
            $table->foreignId('connected_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qb_connections');
    }
};
