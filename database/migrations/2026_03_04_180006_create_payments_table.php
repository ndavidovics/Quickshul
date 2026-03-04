<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('method')->nullable(); // paypal, check, cash, quickbooks, etc.
            $table->string('qb_transaction_id')->nullable()->unique();
            $table->string('paypal_transaction_id')->nullable()->unique();
            $table->string('paypal_order_id')->nullable()->unique(); // pending capture
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('family_id');
            $table->index('payment_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
