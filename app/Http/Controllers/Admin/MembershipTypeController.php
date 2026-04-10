<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipType;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;

class MembershipTypeController extends Controller
{
    public function index(QuickBooksService $qb)
    {
        $types    = MembershipType::orderBy('sort_order')->get();
        $qbLabels = [];

        if (app()->bound('tenant') && app('tenant')->qb_enabled) {
            try {
                $qbLabels = array_values($qb->getCustomerTypes());
            } catch (\Throwable) {
                // QB not connected yet — fine, show manual input instead
            }
        }

        return view('admin.settings.membership', compact('types', 'qbLabels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label'   => 'required|string|max:100',
            'is_donor'=> 'boolean',
        ]);

        $slug = \Str::slug($request->label, '_');

        // Ensure slug is unique within tenant
        $base = $slug;
        $i = 2;
        while (MembershipType::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }

        $max = MembershipType::max('sort_order') ?? 0;

        MembershipType::create([
            'tenant_id'  => app('tenant')->id,
            'slug'       => $slug,
            'label'      => $request->label,
            'is_donor'   => $request->boolean('is_donor'),
            'qb_labels'  => [],
            'sort_order' => $max + 1,
            'active'     => true,
        ]);

        return back()->with('success', "Membership type \"{$request->label}\" added.");
    }

    public function update(Request $request, int $id)
    {
        $type = MembershipType::findOrFail($id);

        $request->validate([
            'label'     => 'required|string|max:100',
            'is_donor'  => 'boolean',
            'qb_labels' => 'nullable|string',
        ]);

        // Parse qb_labels — one per line, trim blanks
        $qbLabels = collect(explode("\n", $request->qb_labels ?? ''))
            ->map('trim')
            ->filter()
            ->values()
            ->all();

        $type->update([
            'label'    => $request->label,
            'is_donor' => $request->boolean('is_donor'),
            'qb_labels'=> $qbLabels,
            'active'   => $request->boolean('active', true),
        ]);

        return back()->with('success', "\"$type->label\" updated.");
    }

    public function destroy(int $id)
    {
        $type = MembershipType::findOrFail($id);

        $inUse = \App\Models\Family::where('membership_type', $type->slug)->count();
        if ($inUse > 0) {
            return back()->withErrors(['error' => "Cannot delete \"{$type->label}\" — {$inUse} families use it. Reassign them first."]);
        }

        $type->delete();
        return back()->with('success', "\"{$type->label}\" deleted.");
    }

    public function reorder(Request $request)
    {
        foreach ($request->order ?? [] as $i => $id) {
            MembershipType::where('id', $id)->update(['sort_order' => $i + 1]);
        }
        return response()->json(['ok' => true]);
    }
}
