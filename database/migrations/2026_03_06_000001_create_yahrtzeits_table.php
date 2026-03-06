<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop death-related columns from family_members
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropIndex(['date_of_death']);
            $table->dropColumn(['date_of_death', 'hebrew_date_of_death', 'hebrew_dod_override']);
        });

        // Dedicated yahrtzeits table
        Schema::create('yahrtzeits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->enum('relationship', ['mother', 'father', 'sister', 'brother', 'child', 'spouse'])->nullable();
            $table->string('full_name');
            $table->string('hebrew_name')->nullable();
            $table->date('date_of_death')->nullable();
            $table->string('hebrew_date_of_death')->nullable(); // full: "15 Tishrei 5785" (auto or manual)
            $table->boolean('hebrew_dod_override')->default(false);
            $table->unsignedTinyInteger('hebrew_month'); // 1–13 (PHP jdtojewish numbering)
            $table->unsignedTinyInteger('hebrew_day');   // 1–30
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yahrtzeits');

        Schema::table('family_members', function (Blueprint $table) {
            $table->date('date_of_death')->nullable();
            $table->string('hebrew_date_of_death')->nullable();
            $table->boolean('hebrew_dod_override')->default(false);
            $table->index('date_of_death');
        });
    }
};
