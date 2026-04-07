<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add time_rules JSON column to calendar_minyanim
        Schema::table('calendar_minyanim', function (Blueprint $table) {
            $table->json('time_rules')->nullable()->after('sat');
        });

        // Create calendar_minyan_exceptions table
        Schema::create('calendar_minyan_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minyan_id')->constrained('calendar_minyanim')->onDelete('cascade');
            $table->string('event_type', 60);
            $table->string('day_type', 20)->default('any');
            $table->string('override_type', 20);
            $table->json('override_value')->nullable();
            $table->unsignedSmallInteger('priority')->default(10);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['minyan_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_minyan_exceptions');

        Schema::table('calendar_minyanim', function (Blueprint $table) {
            $table->dropColumn('time_rules');
        });
    }
};
