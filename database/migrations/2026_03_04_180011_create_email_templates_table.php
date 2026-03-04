<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body'); // HTML/text with merge fields: {family_name}, {balance}, {paypal_link}, {member_name}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
