<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qb_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'family', 'payment'
            $table->unsignedBigInteger('entity_id');
            $table->string('field');
            $table->text('portal_value')->nullable();
            $table->timestamp('portal_updated_at')->nullable();
            $table->text('qb_value')->nullable();
            $table->timestamp('qb_updated_at')->nullable();
            $table->boolean('resolved')->default(false);
            $table->foreignId('resolved_by_user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->enum('resolution', ['portal', 'qb'])->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qb_conflicts');
    }
};
