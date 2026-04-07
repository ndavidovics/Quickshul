<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarHebcalCache;
use App\Models\CalendarMinyan;
use App\Models\CalendarMinyanException;
use App\Models\CalendarSetting;
use App\Services\CalendarService;
use App\Services\HebcalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    // =========================================================================
    // Settings
    // =========================================================================

    public function settings()
    {
        $settings = CalendarSetting::pluck('value', 'key')->all();
        return view('admin.calendar.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $keys = [
            'lat', 'lng', 'timezone',
            'candle_lighting_offset', 'havdala_offset',
            'fast_ends_minor_offset', 'fast_ends_tishabav_offset',
            'school_year_start', 'school_year_end',
            'erev_yk_mincha_default', 'erev_yk_mincha_early', 'erev_yk_mincha_threshold',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                CalendarSetting::set($key, $request->input($key));
            }
        }

        return redirect()->route('admin.calendar.settings')->with('success', 'Calendar settings saved.');
    }

    // =========================================================================
    // Minyanim
    // =========================================================================

    public function minyanim()
    {
        $minyanim = CalendarMinyan::orderBy('sort_order')->orderBy('id')->get();

        if ($minyanim->isEmpty()) {
            $this->seedDefaultMinyanim();
            $minyanim = CalendarMinyan::orderBy('sort_order')->orderBy('id')->get();
        }

        return view('admin.calendar.minyanim', compact('minyanim'));
    }

    protected function seedDefaultMinyanim(): void
    {
        CalendarMinyan::create([
            'name'       => 'Early Shacharis',
            'type'       => 'shacharis',
            'sort_order' => 1,
            'sun'        => '8:00',
            'mon'        => '6:35',
            'tue'        => '6:35',
            'wed'        => '6:35',
            'thu'        => '6:35',
            'fri'        => '6:35',
            'sat'        => '7:45',
            'active'     => true,
            'notes'      => null,
        ]);

        CalendarMinyan::create([
            'name'       => 'Second Shacharis',
            'type'       => 'shacharis',
            'sort_order' => 2,
            'sun'        => null,
            'mon'        => '8:00',
            'tue'        => '8:00',
            'wed'        => '8:00',
            'thu'        => '8:00',
            'fri'        => null,
            'sat'        => '9:15',
            'active'     => true,
            'notes'      => 'Only during summer/school breaks on weekdays',
        ]);

        CalendarMinyan::create([
            'name'       => 'Mincha',
            'type'       => 'mincha',
            'sort_order' => 3,
            'active'     => true,
            'notes'      => 'Computed dynamically from sunset',
        ]);

        CalendarMinyan::create([
            'name'       => 'Maariv',
            'type'       => 'maariv',
            'sort_order' => 4,
            'active'     => true,
            'notes'      => 'Computed dynamically from sunset',
        ]);
    }

    public function storeMinyan(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:shacharis,mincha,maariv,other',
            'sort_order' => 'nullable|integer',
            'sun'        => 'nullable|string|max:50',
            'mon'        => 'nullable|string|max:50',
            'tue'        => 'nullable|string|max:50',
            'wed'        => 'nullable|string|max:50',
            'thu'        => 'nullable|string|max:50',
            'fri'        => 'nullable|string|max:50',
            'sat'        => 'nullable|string|max:50',
            'active'     => 'nullable|boolean',
            'notes'      => 'nullable|string',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['active']     = $request->boolean('active', true);

        CalendarMinyan::create($validated);

        return redirect()->route('admin.calendar.minyanim')->with('success', 'Minyan added.');
    }

    public function updateMinyan(Request $request, int $id)
    {
        $minyan = CalendarMinyan::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:shacharis,mincha,maariv,other',
            'sort_order' => 'nullable|integer',
            'sun'        => 'nullable|string|max:50',
            'mon'        => 'nullable|string|max:50',
            'tue'        => 'nullable|string|max:50',
            'wed'        => 'nullable|string|max:50',
            'thu'        => 'nullable|string|max:50',
            'fri'        => 'nullable|string|max:50',
            'sat'        => 'nullable|string|max:50',
            'active'     => 'nullable|boolean',
            'notes'      => 'nullable|string',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $minyan->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.calendar.minyanim')->with('success', 'Minyan updated.');
    }

    public function deleteMinyan(int $id)
    {
        CalendarMinyan::findOrFail($id)->delete();
        return redirect()->route('admin.calendar.minyanim')->with('success', 'Minyan deleted.');
    }

    public function reorderMinyanim(Request $request)
    {
        $ids = $request->input('ids', []);
        foreach ($ids as $order => $id) {
            CalendarMinyan::where('id', $id)->update(['sort_order' => $order + 1]);
        }
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Minyan Exceptions (CRUD)
    // =========================================================================

    public function listExceptions(int $id): JsonResponse
    {
        $minyan = CalendarMinyan::findOrFail($id);
        $exceptions = $minyan->exceptions()->orderBy('priority', 'desc')->orderBy('id')->get();
        return response()->json(['success' => true, 'exceptions' => $exceptions]);
    }

    public function storeException(Request $request, int $id): JsonResponse
    {
        $minyan = CalendarMinyan::findOrFail($id);

        $validated = $request->validate([
            'event_type'     => 'required|in:rosh_hashana,yom_kippur,erev_yom_kippur,yom_tov,erev_yom_tov,chol_hamoed,hoshana_raba,rosh_chodesh,chanukah,fast_minor,tisha_bav,purim,selichos,civil_holiday',
            'day_type'       => 'required|in:any,weekday,sunday,shabbos',
            'override_type'  => 'required|in:static,dynamic,relative,prepend,hidden',
            'override_value' => 'nullable|array',
            'priority'       => 'nullable|integer|min:1|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);

        $validated['minyan_id'] = $minyan->id;
        $validated['priority']  = $validated['priority'] ?? 10;

        $exception = CalendarMinyanException::create($validated);

        return response()->json(['success' => true, 'exception' => $exception]);
    }

    public function updateException(Request $request, int $minyanId, int $exceptionId): JsonResponse
    {
        $minyan    = CalendarMinyan::findOrFail($minyanId);
        $exception = CalendarMinyanException::where('minyan_id', $minyan->id)
            ->findOrFail($exceptionId);

        $validated = $request->validate([
            'event_type'     => 'required|in:rosh_hashana,yom_kippur,erev_yom_kippur,yom_tov,erev_yom_tov,chol_hamoed,hoshana_raba,rosh_chodesh,chanukah,fast_minor,tisha_bav,purim,selichos,civil_holiday',
            'day_type'       => 'required|in:any,weekday,sunday,shabbos',
            'override_type'  => 'required|in:static,dynamic,relative,prepend,hidden',
            'override_value' => 'nullable|array',
            'priority'       => 'nullable|integer|min:1|max:100',
            'notes'          => 'nullable|string|max:255',
        ]);

        $validated['priority'] = $validated['priority'] ?? 10;
        $exception->update($validated);

        return response()->json(['success' => true, 'exception' => $exception->fresh()]);
    }

    public function deleteException(int $minyanId, int $exceptionId): JsonResponse
    {
        $minyan    = CalendarMinyan::findOrFail($minyanId);
        $exception = CalendarMinyanException::where('minyan_id', $minyan->id)
            ->findOrFail($exceptionId);
        $exception->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Minyan Time Rules
    // =========================================================================

    public function saveTimeRules(Request $request, int $id): JsonResponse
    {
        $minyan = CalendarMinyan::findOrFail($id);

        $request->validate([
            'time_rules' => 'nullable|array',
        ]);

        $minyan->update(['time_rules' => $request->input('time_rules')]);

        return response()->json(['success' => true, 'time_rules' => $minyan->fresh()->time_rules]);
    }

    // =========================================================================
    // Generate / Preview
    // =========================================================================

    public function generate()
    {
        $currentYear = (int) date('Y');
        // Default to the current school year
        $defaultYear = date('m') >= 9 ? $currentYear : $currentYear - 1;

        $cachedYears = CalendarHebcalCache::orderByDesc('year')->pluck('fetched_at', 'year');

        return view('admin.calendar.generate', compact('defaultYear', 'cachedYears'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = (int) $request->input('year');

        set_time_limit(300);

        /** @var CalendarService $service */
        $service  = app(CalendarService::class);
        $calendar = $service->generateYear($year);

        return view('admin.calendar.preview', compact('calendar', 'year'));
    }

    public function refreshHebcal(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = (int) $request->input('year');

        /** @var HebcalService $hebcal */
        $hebcal = app(HebcalService::class);
        $hebcal->refreshCache($year);
        $hebcal->refreshCache($year + 1);

        return redirect()->route('admin.calendar.generate')->with('success',
            "Hebcal data refreshed for {$year} and " . ($year + 1) . "."
        );
    }
}
