<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_minyanim', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['shacharis', 'mincha', 'maariv', 'other']);
            $table->integer('sort_order')->default(0);
            $table->string('sun', 50)->nullable();
            $table->string('mon', 50)->nullable();
            $table->string('tue', 50)->nullable();
            $table->string('wed', 50)->nullable();
            $table->string('thu', 50)->nullable();
            $table->string('fri', 50)->nullable();
            $table->string('sat', 50)->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_hebcal_cache', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->longText('data');
            $table->timestamp('fetched_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_hebcal_cache');
        Schema::dropIfExists('calendar_minyanim');
        Schema::dropIfExists('calendar_settings');
    }
};
