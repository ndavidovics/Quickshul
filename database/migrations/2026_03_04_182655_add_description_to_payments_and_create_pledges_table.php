<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('method');
            $table->string('qb_sales_receipt_id', 50)->nullable()->unique()->after('qb_transaction_id');
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('qb_invoice_id', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['open', 'paid', 'voided'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['description', 'qb_sales_receipt_id']);
        });
        Schema::dropIfExists('pledges');
    }
};
