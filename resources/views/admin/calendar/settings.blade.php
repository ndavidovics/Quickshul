@extends('layouts.admin')

@section('title', 'Calendar Settings')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Calendar Settings</h1>
        <p class="page-subtitle">Configure location, offsets, and school-year parameters for the shul calendar.</p>
    </div>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('admin.calendar.minyanim') }}" class="btn btn-outline">Minyanim</a>
        <a href="{{ route('admin.calendar.generate') }}" class="btn btn-gold">Generate Calendar</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.calendar.settings.save') }}">
    @csrf

    {{-- Location --}}
    <div class="card">
        <div class="card-title">Location</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Latitude</label>
                <input type="text" name="lat" class="form-control"
                    value="{{ $settings['lat'] ?? '' }}"
                    placeholder="35.11666">
                <small class="text-muted text-sm">Memphis, TN default: 35.11666</small>
            </div>
            <div class="form-group">
                <label class="form-label">Longitude</label>
                <input type="text" name="lng" class="form-control"
                    value="{{ $settings['lng'] ?? '' }}"
                    placeholder="-89.87740">
                <small class="text-muted text-sm">Memphis, TN default: -89.87740</small>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Timezone</label>
            <select name="timezone" class="form-control">
                @php
                    $tz = $settings['timezone'] ?? 'America/Chicago';
                    $zones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA);
                @endphp
                @foreach($zones as $zone)
                    <option value="{{ $zone }}" {{ $tz === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Shabbos / Yom Tov Offsets --}}
    <div class="card">
        <div class="card-title">Shabbos &amp; Yom Tov Times</div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Candle Lighting Offset (min before sunset)</label>
                <input type="number" name="candle_lighting_offset" class="form-control"
                    value="{{ $settings['candle_lighting_offset'] ?? '18' }}"
                    placeholder="18">
            </div>
            <div class="form-group">
                <label class="form-label">Havdala Offset (min after sunset)</label>
                <input type="number" name="havdala_offset" class="form-control"
                    value="{{ $settings['havdala_offset'] ?? '50' }}"
                    placeholder="50">
            </div>
        </div>
    </div>

    {{-- Fast Day Offsets --}}
    <div class="card">
        <div class="card-title">Fast Days</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Minor Fast Ends (min after sunset)</label>
                <input type="number" name="fast_ends_minor_offset" class="form-control"
                    value="{{ $settings['fast_ends_minor_offset'] ?? '42' }}"
                    placeholder="42">
                <small class="text-muted text-sm">Tzom Gedaliah, Asara B'Tevet, Tzom Tammuz, Ta'anis Esther</small>
            </div>
            <div class="form-group">
                <label class="form-label">Tisha B'Av Fast Ends (min after sunset)</label>
                <input type="number" name="fast_ends_tishabav_offset" class="form-control"
                    value="{{ $settings['fast_ends_tishabav_offset'] ?? '50' }}"
                    placeholder="50">
            </div>
        </div>
    </div>

    {{-- School Year --}}
    <div class="card">
        <div class="card-title">School Year</div>
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">School Year Start</label>
                <input type="text" name="school_year_start" class="form-control"
                    value="{{ $settings['school_year_start'] ?? 'third wednesday of August' }}"
                    placeholder="third wednesday of August">
                <small class="text-muted text-sm">PHP strtotime-compatible string (without year)</small>
            </div>
            <div class="form-group">
                <label class="form-label">School Year End</label>
                <input type="text" name="school_year_end" class="form-control"
                    value="{{ $settings['school_year_end'] ?? 'third wednesday of June' }}"
                    placeholder="third wednesday of June">
                <small class="text-muted text-sm">PHP strtotime-compatible string (without year)</small>
            </div>
        </div>
    </div>

    {{-- Erev Yom Kippur --}}
    <div class="card">
        <div class="card-title">Erev Yom Kippur</div>
        <div class="grid-3">
            <div class="form-group">
                <label class="form-label">Mincha Default Time</label>
                <input type="text" name="erev_yk_mincha_default" class="form-control"
                    value="{{ $settings['erev_yk_mincha_default'] ?? '4:30' }}"
                    placeholder="4:30">
            </div>
            <div class="form-group">
                <label class="form-label">Mincha Early Time</label>
                <input type="text" name="erev_yk_mincha_early" class="form-control"
                    value="{{ $settings['erev_yk_mincha_early'] ?? '4:15' }}"
                    placeholder="4:15">
                <small class="text-muted text-sm">Used when candle lighting is before threshold</small>
            </div>
            <div class="form-group">
                <label class="form-label">Candle Lighting Threshold</label>
                <input type="text" name="erev_yk_mincha_threshold" class="form-control"
                    value="{{ $settings['erev_yk_mincha_threshold'] ?? '6:20' }}"
                    placeholder="6:20">
                <small class="text-muted text-sm">If candle lighting &lt; this time, use early mincha</small>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem">
        <button type="submit" class="btn btn-gold">Save Settings</button>
    </div>
</form>
@endsection
