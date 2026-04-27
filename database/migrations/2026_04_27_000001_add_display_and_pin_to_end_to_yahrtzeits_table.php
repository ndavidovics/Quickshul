<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->boolean('display')->default(false)->after('notes');
            $table->boolean('pin_to_end')->default(false)->after('display');
        });

        // Auto-enable display for records that already have a date of death
        DB::table('yahrtzeits')->whereNotNull('date_of_death')->update(['display' => true]);
    }

    public function down(): void
    {
        Schema::table('yahrtzeits', function (Blueprint $table) {
            $table->dropColumn(['display', 'pin_to_end']);
        });
    }
};
