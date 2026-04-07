<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique(); // subdomain slug e.g. "beth-israel"
            $table->string('name'); // "Beth Israel Congregation"
            $table->string('tagline')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('primary_color', 7)->default('#1a2d5a'); // hex
            $table->string('org_address')->nullable();
            $table->string('org_city')->nullable();
            $table->string('org_state', 2)->nullable();
            $table->string('org_zip', 10)->nullable();
            $table->string('org_phone')->nullable();
            $table->string('org_email')->nullable();
            $table->string('timezone', 50)->default('America/New_York');
            $table->string('locale', 10)->default('en');
            // Gmail API (per-tenant sending)
            $table->text('gmail_access_token')->nullable();
            $table->text('gmail_refresh_token')->nullable();
            $table->timestamp('gmail_token_expires_at')->nullable();
            $table->string('gmail_email')->nullable();
            // PayPal (per-tenant merchant)
            $table->string('paypal_client_id')->nullable();
            $table->text('paypal_secret')->nullable();
            $table->string('paypal_mode', 10)->default('live'); // sandbox or live
            $table->string('paypal_webhook_id')->nullable();
            // QuickBooks (optional)
            $table->boolean('qb_enabled')->default(false);
            // Onboarding
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('slug', 50); // e.g. full_family, single, donor
            $table->string('label'); // e.g. "Full Family", "Single Member"
            $table->boolean('is_donor')->default(false); // excluded from portal invites / member features
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_types');
        Schema::dropIfExists('tenants');
    }
};
