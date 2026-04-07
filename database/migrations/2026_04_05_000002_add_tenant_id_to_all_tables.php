<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── families ─────────────────────────────────────────────────────────
        Schema::table('families', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // ── users ─────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->boolean('is_tenant_owner')->default(false)->after('is_admin');
            $table->boolean('is_super_admin')->default(false)->after('is_tenant_owner');
            // Drop old global-unique index and replace with per-tenant uniqueness
            $table->dropUnique('users_email_unique');
            $table->unique(['tenant_id', 'email']);
            $table->index('tenant_id');
        });

        // ── payments ──────────────────────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // ── pledges ───────────────────────────────────────────────────────────
        Schema::table('pledges', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // ── payment_tokens ────────────────────────────────────────────────────
        Schema::table('payment_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── yahrtzeits ────────────────────────────────────────────────────────
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── family_emails ─────────────────────────────────────────────────────
        Schema::table('family_emails', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── family_members ────────────────────────────────────────────────────
        Schema::table('family_members', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── qb_connections ────────────────────────────────────────────────────
        Schema::table('qb_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── qb_sync_logs ──────────────────────────────────────────────────────
        Schema::table('qb_sync_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── qb_conflicts ──────────────────────────────────────────────────────
        Schema::table('qb_conflicts', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── audit_logs ────────────────────────────────────────────────────────
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // ── email_templates ───────────────────────────────────────────────────
        Schema::table('email_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── email_sends ───────────────────────────────────────────────────────
        Schema::table('email_sends', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // ── calendar_settings ─────────────────────────────────────────────────
        Schema::table('calendar_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── calendar_minyanim ─────────────────────────────────────────────────
        Schema::table('calendar_minyanim', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── calendar_minyan_exceptions ────────────────────────────────────────
        Schema::table('calendar_minyan_exceptions', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── calendar_hebcal_cache ─────────────────────────────────────────────
        Schema::table('calendar_hebcal_cache', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // ── member_applications ───────────────────────────────────────────────
        Schema::table('member_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('member_applications', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('calendar_hebcal_cache', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('calendar_minyan_exceptions', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('calendar_minyanim', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('calendar_settings', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('email_sends', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('qb_conflicts', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('qb_sync_logs', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('qb_connections', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('family_members', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('family_emails', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('payment_tokens', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('pledges', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn(['tenant_id', 'is_tenant_owner', 'is_super_admin']);
            $table->string('email')->unique()->change();
        });

        Schema::table('families', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
