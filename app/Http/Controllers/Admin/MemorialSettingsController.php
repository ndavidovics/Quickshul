<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarSetting;
use Illuminate\Http\Request;

class MemorialSettingsController extends Controller
{
    public function edit()
    {
        $rows = (int) CalendarSetting::get('memorial_rows', 11);
        $cols = (int) CalendarSetting::get('memorial_cols', 3);

        return view('admin.memorial.settings', compact('rows', 'cols'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rows' => 'required|integer|min:1|max:20',
            'cols' => 'required|integer|min:1|max:6',
        ]);

        CalendarSetting::set('memorial_rows', $validated['rows']);
        CalendarSetting::set('memorial_cols', $validated['cols']);

        return back()->with('success', 'Memorial board settings saved.');
    }
}
