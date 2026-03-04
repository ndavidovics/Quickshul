<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('phone')->nullable();
            $table->enum('membership_type', ['full_family', 'single', 'associate', 'first_year_free'])->default('associate');
            $table->date('member_since')->nullable();
            $table->decimal('total_pledged', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('outstanding_balance', 10, 2)->default(0);
            $table->string('qb_customer_id')->nullable()->unique();
            $table->string('qb_sync_token')->nullable();
            $table->timestamp('qb_last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
