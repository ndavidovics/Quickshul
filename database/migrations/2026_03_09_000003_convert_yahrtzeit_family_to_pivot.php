<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. family_yahrtzeit pivot ───────────────────────────────────────
        Schema::dropIfExists('family_yahrtzeit');
        Schema::create('family_yahrtzeit', function (Blueprint $table) {
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yahrtzeit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['family_id', 'yahrtzeit_id']);
        });

        // Migrate existing family_id → pivot
        DB::table('yahrtzeits')
            ->whereNotNull('family_id')
            ->orderBy('id')
            ->each(function ($yahrtzeit) {
                DB::table('family_yahrtzeit')->insert([
                    'family_id'    => $yahrtzeit->family_id,
                    'yahrtzeit_id' => $yahrtzeit->id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            });

        // Drop family_id from yahrtzeits
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropColumn('family_id');
        });

        // ── 2. family_member_yahrtzeit pivot ────────────────────────────────
        Schema::create('family_member_yahrtzeit', function (Blueprint $table) {
            $table->foreignId('family_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('yahrtzeit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['family_member_id', 'yahrtzeit_id']);
        });

        // Migrate existing family_member_id → pivot
        DB::table('yahrtzeits')
            ->whereNotNull('family_member_id')
            ->orderBy('id')
            ->each(function ($yahrtzeit) {
                DB::table('family_member_yahrtzeit')->insert([
                    'family_member_id' => $yahrtzeit->family_member_id,
                    'yahrtzeit_id'     => $yahrtzeit->id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            });

        // Drop family_member_id from yahrtzeits
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->dropForeign(['family_member_id']);
            $table->dropColumn('family_member_id');
        });
    }

    public function down(): void
    {
        // ── Restore family_member_id ────────────────────────────────────────
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->foreignId('family_member_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
            $table->index('family_member_id');
        });

        DB::table('yahrtzeits')->orderBy('id')->each(function ($yahrtzeit) {
            $memberId = DB::table('family_member_yahrtzeit')
                ->where('yahrtzeit_id', $yahrtzeit->id)
                ->orderBy('family_member_id')
                ->value('family_member_id');

            if ($memberId) {
                DB::table('yahrtzeits')
                    ->where('id', $yahrtzeit->id)
                    ->update(['family_member_id' => $memberId]);
            }
        });

        Schema::dropIfExists('family_member_yahrtzeit');

        // ── Restore family_id ───────────────────────────────────────────────
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->constrained()->cascadeOnDelete()->after('id');
            $table->index('family_id');
        });

        DB::table('yahrtzeits')->orderBy('id')->each(function ($yahrtzeit) {
            $familyId = DB::table('family_yahrtzeit')
                ->where('yahrtzeit_id', $yahrtzeit->id)
                ->orderBy('family_id')
                ->value('family_id');

            if ($familyId) {
                DB::table('yahrtzeits')
                    ->where('id', $yahrtzeit->id)
                    ->update(['family_id' => $familyId]);
            }
        });

        Schema::dropIfExists('family_yahrtzeit');
    }
};
