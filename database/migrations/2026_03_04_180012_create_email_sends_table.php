<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->nullOnDelete()->constrained('email_templates');
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'sent_at']);
            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sends');
    }
};
