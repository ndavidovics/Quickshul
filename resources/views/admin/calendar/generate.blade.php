@extends('layouts.admin')

@section('title', 'Generate Calendar')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Generate Shul Calendar</h1>
        <p class="page-subtitle">Generate a printable minyan schedule calendar for a school year (September – August).</p>
    </div>
    <div style="display:flex;gap:.75rem">
        <a href="{{ route('admin.calendar.settings') }}" class="btn btn-outline">Settings</a>
        <a href="{{ route('admin.calendar.minyanim') }}" class="btn btn-outline">Minyanim</a>
    </div>
</div>

<div class="card">
    <div class="card-title">Select School Year</div>
    <form method="POST" action="{{ route('admin.calendar.preview') }}" target="_blank">
        @csrf
        <div style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
            <div class="form-group" style="margin:0;flex:0 0 220px">
                <label class="form-label">School Year Starting</label>
                <select name="year" class="form-control">
                    @for($y = 2022; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $y == $defaultYear ? 'selected' : '' }}>
                            {{ $y }} – {{ $y + 1 }} (Sep {{ $y }} – Aug {{ $y + 1 }})
                        </option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-gold" style="height:40px">
                Preview Calendar (new tab)
            </button>
        </div>
    </form>
</div>

<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Hebcal Data Cache</div>
    <p class="text-muted text-sm" style="margin-bottom:1rem">
        The calendar uses cached holiday data from Hebcal.com. Refresh if data seems stale (cache expires after 30 days automatically).
    </p>
    @if($cachedYears->isNotEmpty())
    <table class="table" style="margin-bottom:1rem">
        <thead>
            <tr>
                <th>Year</th>
                <th>Cached At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($cachedYears as $yr => $fetchedAt)
            <tr>
                <td>{{ $yr }}</td>
                <td>{{ $fetchedAt ? \Carbon\Carbon::parse($fetchedAt)->format('M j, Y g:i A') : '—' }}</td>
                <td>
                    @if($fetchedAt && \Carbon\Carbon::parse($fetchedAt)->gt(\Carbon\Carbon::now()->subDays(30)))
                        <span class="badge badge-green">Fresh</span>
                    @else
                        <span class="badge badge-red">Stale</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p class="text-muted text-sm">No cached data yet. Generate a calendar to populate the cache.</p>
    @endif

    <form method="POST" action="{{ route('admin.calendar.hebcal.refresh') }}">
        @csrf
        <div style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
            <div class="form-group" style="margin:0;flex:0 0 220px">
                <label class="form-label">Refresh for School Year</label>
                <select name="year" class="form-control">
                    @for($y = 2022; $y <= 2030; $y++)
                        <option value="{{ $y }}" {{ $y == $defaultYear ? 'selected' : '' }}>
                            {{ $y }} – {{ $y + 1 }}
                        </option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height:40px">
                Refresh Hebcal Data
            </button>
        </div>
    </form>
</div>
@endsection
