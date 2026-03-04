<?php

namespace App\Console\Commands;

use App\Jobs\DailyQuickBooksSync;
use App\Models\Family;
use App\Models\FamilyMember;
use Illuminate\Console\Command;

class ParseFamilyNames extends Command
{
    protected $signature   = 'members:parse-names {--force : Re-parse even families that already have members}';
    protected $description = 'Auto-create family members from QB display names for families with no members';

    public function handle(): void
    {
        $query = Family::query();

        if (!$this->option('force')) {
            $query->whereDoesntHave('members');
        }

        $families = $query->get();
        $parser   = new DailyQuickBooksSync();
        $created  = 0;

        foreach ($families as $family) {
            if ($this->option('force')) {
                // Skip families that already have members unless forced
                if ($family->members()->count() > 0) continue;
            }

            $members = $parser->parseFamilyNameToMembers($family->name);

            foreach ($members as $m) {
                FamilyMember::create(array_merge($m, ['family_id' => $family->id]));
                $created++;
            }

            $this->line("  {$family->name} → " . count($members) . ' member(s)');
        }

        $this->info("Done. Created {$created} family members across {$families->count()} families.");
    }
}
