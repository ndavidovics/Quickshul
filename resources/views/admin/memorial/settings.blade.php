@extends('layouts.admin')
@section('title', 'Memorial Board Settings')

@section('content')
<div style="max-width:520px">
    <h1 class="page-title">Memorial Board Settings</h1>
    <p class="page-subtitle">Control how plaques are laid out on the TV display.</p>

    <div class="card">
        <div class="card-title">Display Layout</div>
        <p class="text-muted text-sm" style="margin-bottom:1.5rem">
            Controls how many plaques appear on each slide of the memorial board.
            The board is designed for a 1920&times;1080 display with a 1500px wide plaque area.
        </p>

        <form method="POST" action="{{ route('admin.memorial.settings.save') }}">
            @csrf

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Rows per Slide</label>
                    <input type="number" name="rows" class="form-control" min="1" max="20"
                           value="{{ old('rows', $rows) }}">
                    <div class="text-muted text-sm" style="margin-top:0.25rem">How many rows of plaques fit vertically (currently {{ $rows }})</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Columns per Slide</label>
                    <input type="number" name="cols" class="form-control" min="1" max="6"
                           value="{{ old('cols', $cols) }}">
                    <div class="text-muted text-sm" style="margin-top:0.25rem">How many columns fit across (currently {{ $cols }})</div>
                </div>
            </div>

            @php
                $perSlide     = $rows * $cols;
                $plaqueWidth  = (int) floor((1500 - $cols * 10) / $cols) - 14;
                $plaqueHeight = (int) floor(1035 / $rows) - 23;
                $total        = \App\Models\Yahrtzeit::where('display', true)->count();
                $slideCount   = $perSlide > 0 ? (int) ceil($total / $perSlide) : 0;
            @endphp

            <div style="background:var(--bg-card2);border-radius:6px;padding:1rem;margin-bottom:1.25rem;font-size:0.875rem;color:var(--text-muted)">
                <strong style="color:var(--text)">Current result:</strong>
                {{ $perSlide }} plaques per slide &bull;
                {{ $total }} total displayed yahrtzeits &bull;
                {{ $slideCount }} slides &bull;
                {{ $plaqueWidth }}px &times; {{ $plaqueHeight }}px plaques
            </div>

            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn-gold">Save Settings</button>
                <a href="{{ route('memorial') }}" target="_blank" class="btn btn-outline">Preview Board ↗</a>
            </div>
        </form>
    </div>
</div>
@endsection
