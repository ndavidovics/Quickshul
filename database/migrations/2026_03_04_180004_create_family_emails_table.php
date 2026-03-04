<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('email')->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['family_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_emails');
    }
};
