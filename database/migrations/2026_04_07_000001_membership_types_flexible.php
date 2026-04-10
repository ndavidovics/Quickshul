<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add QB label mapping to membership_types
        Schema::table('membership_types', function (Blueprint $table) {
            $table->json('qb_labels')->nullable()->after('is_donor')
                  ->comment('QB CustomerType names that map to this type');
        });

        // Change families.membership_type from ENUM to VARCHAR so any tenant slug works
        Schema::table('families', function (Blueprint $table) {
            $table->string('membership_type', 50)->default('donor')->change();
        });

        // Seed yiomtest tenant (id=1) with QB label mappings now that the column exists
        DB::table('membership_types')->where('tenant_id', 1)->get()
            ->each(function ($mt) {
                $map = match ($mt->slug) {
                    'full_family'     => ['Member Family', 'Associate Membership, Member Family', 'Complimentary, Member Family', 'Free 1 year membersh, Member Family'],
                    'single'          => ['Single Member', 'Free 1 year membersh, Single Member'],
                    'associate'       => ['Associate Membership', 'Associate Membership, H/M Donation'],
                    'first_year_free' => ['Complimentary', 'Complimentary, Free 1 year membersh', 'Complimentary, H/M Donation', 'Aliyah Donation, Complimentary'],
                    'donor'           => [],
                    default           => [],
                };
                DB::table('membership_types')
                    ->where('id', $mt->id)
                    ->update(['qb_labels' => json_encode($map)]);
            });
    }

    public function down(): void
    {
        Schema::table('membership_types', function (Blueprint $table) {
            $table->dropColumn('qb_labels');
        });
    }
};
