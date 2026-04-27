<?php

namespace App\Http\Controllers;

use App\Models\CalendarSetting;
use App\Models\Yahrtzeit;
use Illuminate\Support\Facades\Cache;

class MemorialController extends Controller
{
    private function settings(): array
    {
        $rows = (int) CalendarSetting::get('memorial_rows', 11);
        $cols = (int) CalendarSetting::get('memorial_cols', 3);

        // Width: fill right_side (1500px) with $cols columns, accounting for border (14px) and margin (10px)
        $plaqueWidth  = (int) floor((1500 - $cols * 10) / $cols) - 14;

        // Available height = body(1080) - right_side margin-top(45) = 1035px
        // Per row = plaque height + border top+bottom(14) + margin-bottom(9) = h + 23
        $plaqueHeight = (int) floor(1035 / $rows) - 23;

        // Scale fonts proportionally from the 70px baseline
        $scale      = $plaqueHeight / 70;
        $nameSize   = (int) round(22 * $scale);
        $hebSize    = (int) round(28 * $scale);
        $smengSize  = (int) round(16 * $scale);
        $smhebSize  = (int) round(20 * $scale);
        $nameMargin = (int) round(3 * $scale);

        return [
            'perSlide'    => $rows * $cols,
            'plaqueWidth' => $plaqueWidth,
            'plaqueHeight'=> $plaqueHeight,
            'nameSize'    => $nameSize,
            'hebSize'     => $hebSize,
            'smengSize'   => $smengSize,
            'smhebSize'   => $smhebSize,
            'nameMargin'  => $nameMargin,
        ];
    }

    public function index()
    {
        $settings   = $this->settings();
        $yahrtzeits = $this->getYahrtzeits();
        $slideCount = (int) ceil($yahrtzeits->count() / $settings['perSlide']);
        $mishnaYomi = $this->getMishnaYomi();
        $today      = $this->todayHebrew();

        return view('memorial.index', compact('slideCount', 'mishnaYomi', 'today'));
    }

    public function slide(int $n)
    {
        $settings     = $this->settings();
        $perSlide     = $settings['perSlide'];
        $plaqueWidth  = $settings['plaqueWidth'];
        $plaqueHeight = $settings['plaqueHeight'];
        $nameSize     = $settings['nameSize'];
        $hebSize      = $settings['hebSize'];
        $smengSize    = $settings['smengSize'];
        $smhebSize    = $settings['smhebSize'];
        $nameMargin   = $settings['nameMargin'];

        $yahrtzeits = $this->getYahrtzeits();
        $today      = $this->todayHebrew();
        $slideCount = (int) ceil($yahrtzeits->count() / $perSlide);

        $offset  = ($n - 1) * $perSlide;
        $records = $yahrtzeits->slice($offset, $perSlide)->values();

        return view('memorial._slide', compact(
            'records', 'today', 'slideCount', 'n',
            'plaqueWidth', 'plaqueHeight', 'nameSize', 'hebSize', 'smengSize', 'smhebSize', 'nameMargin'
        ));
    }

    // -------------------------------------------------------------------------

    private function getYahrtzeits()
    {
        $all    = Yahrtzeit::where('display', true)->get();
        $normal = $all->filter(fn($y) => !$y->pin_to_end)
                      ->sortBy(fn($y) => strtolower(strrchr(' ' . trim($y->full_name), ' ')))
                      ->values();
        $pinned = $all->filter(fn($y) => $y->pin_to_end)->values();

        return $normal->concat($pinned);
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
