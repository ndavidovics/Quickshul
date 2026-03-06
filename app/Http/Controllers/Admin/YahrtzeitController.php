<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Yahrtzeit;
use App\Services\AuditService;
use App\Services\HebrewDateService;
use Illuminate\Http\Request;

class YahrtzeitController extends Controller
{
    private const MONTH_NAMES = [
        1=>'Tishrei',2=>'Cheshvan',3=>'Kislev',4=>'Tevet',5=>'Shevat',
        6=>'Adar I',7=>'Adar / Adar II',8=>'Nisan',9=>'Iyar',10=>'Sivan',
        11=>'Tammuz',12=>'Av',13=>'Elul',
    ];

    public function __construct(
        private AuditService $audit,
        private HebrewDateService $hebrewDate
    ) {}

    public function index(Request $request)
    {
        $query = Yahrtzeit::with('family')->orderBy('hebrew_month')->orderBy('hebrew_day');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('hebrew_name', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dod = $request->has_dod) {
            if ($dod === 'yes') {
                $query->whereNotNull('date_of_death');
            } elseif ($dod === 'no') {
                $query->whereNull('date_of_death');
            }
        }

        $yahrtzeits = $query->paginate(30)->withQueryString();
        $families   = Family::orderBy('name')->get(['id', 'name']);

        return view('admin.yahrtzeits.index', compact('yahrtzeits', 'families'));
    }

    public function export(Request $request)
    {
        $query = Yahrtzeit::with('family')->orderBy('hebrew_month')->orderBy('hebrew_day');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('hebrew_name', 'like', "%{$search}%")
                  ->orWhereHas('family', fn($fq) => $fq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($dod = $request->has_dod) {
            if ($dod === 'yes') {
                $query->whereNotNull('date_of_death');
            } elseif ($dod === 'no') {
                $query->whereNull('date_of_death');
            }
        }

        $yahrtzeits = $query->get();
        $months     = self::MONTH_NAMES;

        return response()->streamDownload(function () use ($yahrtzeits, $months) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel renders Hebrew correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Name', 'Hebrew Name', 'Family', 'Relationship', 'Date of Death', 'Hebrew Date', 'Annual Yahrtzeit']);
            foreach ($yahrtzeits as $y) {
                fputcsv($out, [
                    $y->full_name,
                    $y->hebrew_name ?? '',
                    $y->family?->name ?? '',
                    $y->relationship_label ?? '',
                    $y->date_of_death?->format('Y-m-d') ?? '',
                    $y->hebrew_date_of_death ?? '',
                    ($y->hebrew_day . ' ' . ($months[$y->hebrew_month] ?? '')),
                ]);
            }
            fclose($out);
        }, 'yahrtzeits.csv', ['Content-Type' => 'text/csv']);
    }

    public function storeGlobal(Request $request)
    {
        $validated = $request->validate([
            'family_id'            => 'required|exists:families,id',
            'full_name'            => 'required|string|max:255',
            'hebrew_name'          => 'nullable|string|max:255',
            'relationship'         => 'nullable|in:mother,father,sister,brother,child,spouse',
            'date_of_death'        => 'nullable|date',
            'hebrew_date_of_death' => 'nullable|string|max:100',
            'hebrew_dod_override'  => 'boolean',
            'hebrew_month'         => 'required_without:date_of_death|nullable|integer|between:1,13',
            'hebrew_day'           => 'required_without:date_of_death|nullable|integer|between:1,30',
            'notes'                => 'nullable|string',
        ]);

        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $h = $this->hebrewDate->gregorianToHebrew($validated['date_of_death']);
            $validated['hebrew_date_of_death'] = "{$h['day']} {$h['month_name']} {$h['year']}";
            $validated['hebrew_month']         = $h['month'];
            $validated['hebrew_day']           = $h['day'];
        } elseif (!empty($validated['hebrew_dod_override']) && !empty($validated['hebrew_date_of_death'])) {
            [$hDay, $hMonth] = $this->parseHebrewDateString($validated['hebrew_date_of_death']);
            if ($hDay && $hMonth) {
                $validated['hebrew_month'] = $hMonth;
                $validated['hebrew_day']   = $hDay;
            }
        }

        $family    = Family::findOrFail($validated['family_id']);
        $yahrtzeit = Yahrtzeit::create($validated);
        $this->audit->log('yahrtzeit.created', $yahrtzeit, [], $yahrtzeit->toArray(), "Added yahrtzeit {$yahrtzeit->full_name} for {$family->name}");

        return redirect()->route('admin.yahrtzeits.index')->with('success', "Yahrtzeit \"{$yahrtzeit->full_name}\" added for {$family->name}.");
    }

    public function store(Request $request, int $familyId)
    {
        $family = Family::findOrFail($familyId);

        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'hebrew_name'          => 'nullable|string|max:255',
            'family_member_id'     => 'nullable|exists:family_members,id',
            'relationship'         => 'nullable|in:mother,father,sister,brother,child,spouse',
            'date_of_death'        => 'nullable|date',
            'hebrew_date_of_death' => 'nullable|string|max:100',
            'hebrew_dod_override'  => 'boolean',
            'hebrew_month'         => 'required_without:date_of_death|nullable|integer|between:1,13',
            'hebrew_day'           => 'required_without:date_of_death|nullable|integer|between:1,30',
            'notes'                => 'nullable|string',
        ]);

        $validated['family_id'] = $familyId;

        // If Gregorian date given without override, auto-compute Hebrew fields
        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $h = $this->hebrewDate->gregorianToHebrew($validated['date_of_death']);
            $validated['hebrew_date_of_death'] = "{$h['day']} {$h['month_name']} {$h['year']}";
            $validated['hebrew_month']         = $h['month'];
            $validated['hebrew_day']           = $h['day'];
        } elseif (!empty($validated['hebrew_dod_override']) && !empty($validated['hebrew_date_of_death'])) {
            // Override: parse month/day from the entered Hebrew date string
            [$hDay, $hMonth] = $this->parseHebrewDateString($validated['hebrew_date_of_death']);
            if ($hDay && $hMonth) {
                $validated['hebrew_month'] = $hMonth;
                $validated['hebrew_day']   = $hDay;
            }
        }

        $yahrtzeit = Yahrtzeit::create($validated);
        $this->audit->log('yahrtzeit.created', $yahrtzeit, [], $yahrtzeit->toArray(), "Added yahrtzeit {$yahrtzeit->full_name} for {$family->name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Yahrtzeit added.');
    }

    public function edit(int $familyId, int $yid)
    {
        $family    = Family::with('members')->findOrFail($familyId);
        $yahrtzeit = Yahrtzeit::where('family_id', $familyId)->findOrFail($yid);

        return view('admin.yahrtzeits.edit', compact('family', 'yahrtzeit'));
    }

    public function update(Request $request, int $familyId, int $yid)
    {
        $yahrtzeit = Yahrtzeit::where('family_id', $familyId)->findOrFail($yid);
        $old       = $yahrtzeit->toArray();

        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'hebrew_name'          => 'nullable|string|max:255',
            'family_member_id'     => 'nullable|exists:family_members,id',
            'relationship'         => 'nullable|in:mother,father,sister,brother,child,spouse',
            'date_of_death'        => 'nullable|date',
            'hebrew_date_of_death' => 'nullable|string|max:100',
            'hebrew_dod_override'  => 'boolean',
            'hebrew_month'         => 'required_without:date_of_death|nullable|integer|between:1,13',
            'hebrew_day'           => 'required_without:date_of_death|nullable|integer|between:1,30',
            'notes'                => 'nullable|string',
        ]);

        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $h = $this->hebrewDate->gregorianToHebrew($validated['date_of_death']);
            $validated['hebrew_date_of_death'] = "{$h['day']} {$h['month_name']} {$h['year']}";
            $validated['hebrew_month']         = $h['month'];
            $validated['hebrew_day']           = $h['day'];
        } elseif (!empty($validated['hebrew_dod_override']) && !empty($validated['hebrew_date_of_death'])) {
            [$hDay, $hMonth] = $this->parseHebrewDateString($validated['hebrew_date_of_death']);
            if ($hDay && $hMonth) {
                $validated['hebrew_month'] = $hMonth;
                $validated['hebrew_day']   = $hDay;
            }
        }

        $yahrtzeit->update($validated);
        $this->audit->logModelChange($yahrtzeit, $old, $yahrtzeit->fresh()->toArray());

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Yahrtzeit updated.');
    }

    public function destroy(int $familyId, int $yid)
    {
        $yahrtzeit = Yahrtzeit::where('family_id', $familyId)->findOrFail($yid);
        $name      = $yahrtzeit->full_name;
        $yahrtzeit->delete();
        $this->audit->log('yahrtzeit.deleted', $yahrtzeit, $yahrtzeit->toArray(), [], "Deleted yahrtzeit {$name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Yahrtzeit removed.');
    }

    private function parseHebrewDateString(string $str): array
    {
        // Expects "15 Tishrei 5785" format
        $parts = explode(' ', trim($str));
        if (count($parts) < 2) {
            return [null, null];
        }

        $monthNames = [
            'tishrei' => 1, 'tishri' => 1,
            'cheshvan' => 2, 'heshvan' => 2, 'marcheshvan' => 2,
            'kislev' => 3,
            'tevet' => 4,
            'shevat' => 5,
            'adar i' => 6, 'adar 1' => 6,
            'adar' => 7, 'adar ii' => 7, 'adar 2' => 7,
            'nisan' => 8,
            'iyar' => 9,
            'sivan' => 10,
            'tammuz' => 11,
            'av' => 12,
            'elul' => 13,
        ];

        $day   = (int) $parts[0];
        $month = $monthNames[strtolower($parts[1])] ?? null;

        return [$day ?: null, $month];
    }
}
