<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_applications', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('membership_type');
            $table->json('data');                          // all form fields
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('family_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_applications');
    }
};
