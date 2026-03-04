<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('hebrew_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('other');
            $table->enum('role', ['parent', 'child', 'other'])->default('other');
            $table->date('date_of_birth')->nullable();
            $table->string('hebrew_date_of_birth')->nullable(); // e.g. "15 Tishrei 5785"
            $table->boolean('hebrew_dob_override')->default(false);
            $table->date('date_of_death')->nullable();
            $table->string('hebrew_date_of_death')->nullable();
            $table->boolean('hebrew_dod_override')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['family_id', 'deleted_at']);
            $table->index('date_of_death');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
