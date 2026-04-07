<?php

namespace App\Services;

use App\Models\CalendarHebcalCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HebcalService
{
    /**
     * Returns all Hebcal items for the given Gregorian year
     * by fetching all 12 months and merging them.
     */
    public function getForYear(int $year): array
    {
        if ($this->isCacheStale($year)) {
            $this->refreshCache($year);
        }

        $record = CalendarHebcalCache::where('year', $year)->first();
        if (!$record) {
            return [];
        }

        $decoded = json_decode($record->data, true);
        return $decoded['items'] ?? [];
    }

    public function isCacheStale(int $year): bool
    {
        $record = CalendarHebcalCache::where('year', $year)->first();
        if (!$record) {
            return true;
        }
        return $record->fetched_at->lt(Carbon::now()->subDays(30));
    }

    public function refreshCache(int $year): void
    {
        $allItems = [];

        for ($month = 1; $month <= 12; $month++) {
            $url = "https://www.hebcal.com/hebcal/?v=1&cfg=json&nh=on&nx=on&year={$year}&month={$month}&ss=on&mf=on&s=on&i=off&lg=a&F=on";

            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 15,
                        'header'  => "User-Agent: YIOM-Portal/1.0\r\n",
                    ],
                    'ssl' => [
                        'verify_peer'      => true,
                        'verify_peer_name' => true,
                    ],
                ]);

                $json = @file_get_contents($url, false, $context);
                if ($json === false) {
                    Log::warning("HebcalService: failed to fetch month {$month} for year {$year}");
                    continue;
                }

                $decoded = json_decode($json, true);
                if (!empty($decoded['items'])) {
                    $allItems = array_merge($allItems, $decoded['items']);
                }
            } catch (\Throwable $e) {
                Log::error("HebcalService exception for year {$year} month {$month}: " . $e->getMessage());
            }
        }

        $data = json_encode(['items' => $allItems]);

        CalendarHebcalCache::updateOrCreate(
            ['year' => $year],
            [
                'data'       => $data,
                'fetched_at' => Carbon::now(),
            ]
        );
    }
}
