<?php

namespace App\Http\Controllers;

use App\Models\Yahrtzeit;
use Illuminate\Support\Facades\Cache;

class MemorialController extends Controller
{
    private const PER_SLIDE = 44;

    public function index()
    {
        $yahrtzeits = $this->getYahrtzeits();
        $slideCount  = (int) ceil($yahrtzeits->count() / self::PER_SLIDE);
        $mishnaYomi  = $this->getMishnaYomi();
        $today       = $this->todayHebrew();

        return view('memorial.index', compact('slideCount', 'mishnaYomi', 'today'));
    }

    public function slide(int $n)
    {
        $yahrtzeits = $this->getYahrtzeits();
        $today      = $this->todayHebrew();
        $slideCount = (int) ceil($yahrtzeits->count() / self::PER_SLIDE);

        $offset  = ($n - 1) * self::PER_SLIDE;
        $records = $yahrtzeits->slice($offset, self::PER_SLIDE)->values();

        // Pick plaque size class based on how many names are on this slide
        $count      = $records->count();
        $sizeClass  = match (true) {
            $count === 1  => 'names1',
            $count === 2  => 'names2',
            $count <= 3   => 'names3',
            $count <= 10  => 'names10',
            $count <= 21  => 'names21',
            default       => '',
        };

        return view('memorial._slide', compact('records', 'today', 'sizeClass', 'slideCount', 'n'));
    }

    // -------------------------------------------------------------------------

    private function getYahrtzeits()
    {
        return Yahrtzeit::whereNotNull('date_of_death')
            ->whereNotNull('hebrew_date_of_death')
            ->where('hebrew_date_of_death', '!=', '')
            ->get()
            ->sortBy(fn($y) => strtolower(strrchr(' ' . trim($y->full_name), ' ')))
            ->values();
    }

    private function getMishnaYomi(): string
    {
        return Cache::remember('memorial_mishna_yomi', 3600, function () {
            try {
                $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
                $raw  = @file_get_contents('https://www.torahcalc.com/api/dailylearning', false, $ctx);
                $data = $raw ? json_decode($raw, true) : null;
                return $data['data']['mishnaYomi']['hebrewName'] ?? '';
            } catch (\Throwable) {
                return '';
            }
        });
    }

    private function todayHebrew(): array
    {
        $jd = gregoriantojd((int) date('m'), (int) date('d'), (int) date('Y'));
        [$month, $day] = array_map('intval', explode('/', jdtojewish($jd)));
        return ['month' => $month, 'day' => $day];
    }
}
