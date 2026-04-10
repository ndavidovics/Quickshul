@extends('layouts.admin')
@section('title', 'Membership Types')

@section('content')
<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.75rem">
    <a href="{{ route('admin.settings') }}" class="btn btn-outline btn-sm">← Settings</a>
    <div>
        <h1 class="page-title" style="margin-bottom:0">Membership Types</h1>
        <p class="page-subtitle" style="margin-bottom:0">Define your membership categories and map them to QuickBooks customer types</p>
    </div>
</div>

{{-- QB just-connected onboarding guide --}}
@if(session('qb_just_connected'))
<div style="background:rgba(46,204,113,0.07);border:1px solid rgba(46,204,113,0.35);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.75rem">
        <span style="font-size:1.3rem">🎉</span>
        <div style="font-weight:700;font-size:1rem;color:#2ecc71">QuickBooks connected!</div>
    </div>
    <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:1rem">
        Before running your first sync, map your QuickBooks customer types to membership categories here.
        This tells the sync which QB type (e.g. <em>"Member Family"</em>) corresponds to which membership level (e.g. <em>"Full Member"</em>).
        Families will be classified correctly on import instead of defaulting to <em>donor</em>.
    </p>
    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:var(--text-muted)">
            <span style="background:rgba(46,204,113,0.2);color:#2ecc71;border-radius:50%;width:1.4rem;height:1.4rem;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;flex-shrink:0">1</span>
            Add or review your membership types below
        </div>
        <span style="color:var(--border)">→</span>
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:var(--text-muted)">
            <span style="background:rgba(46,204,113,0.2);color:#2ecc71;border-radius:50%;width:1.4rem;height:1.4rem;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;flex-shrink:0">2</span>
            Paste your QB customer type names into each mapping field
            @if(!empty($qbLabels))<span style="color:var(--gold);font-size:0.75rem">(your QB types are shown on the right →)</span>@endif
        </div>
        <span style="color:var(--border)">→</span>
        <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.82rem;color:var(--text-muted)">
            <span style="background:rgba(46,204,113,0.2);color:#2ecc71;border-radius:50%;width:1.4rem;height:1.4rem;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;flex-shrink:0">3</span>
            Run your first QB sync
        </div>
    </div>
    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(46,204,113,0.15)">
        <a href="{{ route('admin.qb') }}" class="btn btn-outline btn-sm">→ Go to QuickBooks Sync</a>
    </div>
</div>
@endif

@if(session('success'))
<div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.3);border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;color:#2ecc71">
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.3);border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;color:#e74c3c">
    {{ $errors->first() }}
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start">

    {{-- ── Left: existing types ─────────────────────────────── --}}
    <div>
        @foreach($types as $type)
        <div class="card" style="margin-bottom:1rem">
            <form method="POST" action="{{ route('admin.membership-types.update', $type->id) }}">
                @csrf @method('PUT')

                <div style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:start">
                    <div>
                        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap">
                            <div style="font-weight:700;font-size:1rem;color:var(--text)">{{ $type->label }}</div>
                            <span style="font-family:monospace;font-size:0.72rem;color:var(--text-muted);background:rgba(136,153,187,0.1);padding:0.15rem 0.5rem;border-radius:4px">{{ $type->slug }}</span>
                            @if($type->is_donor)
                                <span style="font-size:0.7rem;background:rgba(201,168,76,0.15);color:var(--gold);border-radius:4px;padding:0.1rem 0.4rem">donor</span>
                            @endif
                            @if(!$type->active)
                                <span style="font-size:0.7rem;background:rgba(231,76,60,0.1);color:#e74c3c;border-radius:4px;padding:0.1rem 0.4rem">inactive</span>
                            @endif
                            @php $count = \App\Models\Family::where('membership_type', $type->slug)->count() @endphp
                            <span class="text-sm text-muted">{{ $count }} {{ Str::plural('family', $count) }}</span>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.75rem">
                            <div>
                                <label class="form-label" style="font-size:0.75rem">Label</label>
                                <input type="text" name="label" class="form-control" value="{{ $type->label }}" required>
                            </div>
                            <div style="display:flex;align-items:flex-end;gap:1rem;padding-bottom:0.25rem">
                                <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.85rem;color:var(--text-muted)">
                                    <input type="checkbox" name="is_donor" value="1" {{ $type->is_donor ? 'checked' : '' }}>
                                    Is donor category
                                </label>
                                <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.85rem;color:var(--text-muted)">
                                    <input type="checkbox" name="active" value="1" {{ $type->active ? 'checked' : '' }}>
                                    Active
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" style="font-size:0.75rem">
                                QuickBooks Customer Types that map to this
                                <span class="text-muted" style="font-weight:400">(one per line)</span>
                            </label>
                            <textarea name="qb_labels" class="form-control" rows="3"
                                      style="font-size:0.8rem;font-family:monospace;resize:vertical"
                                      placeholder="Paste QB customer type names here, one per line...">{{ implode("\n", $type->qb_labels ?? []) }}</textarea>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:0.5rem;padding-top:2rem">
                        <button type="submit" class="btn btn-gold btn-sm">Save</button>
                        @if($count === 0)
                        <form method="POST" action="{{ route('admin.membership-types.destroy', $type->id) }}"
                              onsubmit="return confirm('Delete {{ addslashes($type->label) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm" style="width:100%;background:rgba(231,76,60,0.1);color:#e74c3c;border:1px solid rgba(231,76,60,0.25)">Delete</button>
                        </form>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        @endforeach

        @if($types->isEmpty())
        <div class="card" style="text-align:center;padding:2rem;color:var(--text-muted)">
            No membership types defined yet. Add one on the right →
        </div>
        @endif
    </div>

    {{-- ── Right: add new + QB reference ──────────────────────── --}}
    <div>
        <div class="card" style="margin-bottom:1.25rem">
            <div style="font-weight:700;font-size:0.95rem;margin-bottom:1rem">Add Membership Type</div>
            <form method="POST" action="{{ route('admin.membership-types.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Label <span style="color:#e74c3c">*</span></label>
                    <input type="text" name="label" class="form-control" placeholder="e.g. Full Family" required>
                </div>
                <div class="form-group" style="margin-bottom:1rem">
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;font-size:0.875rem;color:var(--text-muted)">
                        <input type="checkbox" name="is_donor" value="1">
                        This is a donor (non-member) category
                    </label>
                </div>
                <button type="submit" class="btn btn-gold btn-sm" style="width:100%">Add Type</button>
            </form>
        </div>

        @if(!empty($qbLabels))
        <div class="card">
            <div style="font-weight:700;font-size:0.85rem;margin-bottom:0.5rem">Your QuickBooks Customer Types</div>
            <p class="text-sm text-muted" style="margin-bottom:0.75rem">Copy and paste these into the QB mapping fields above.</p>
            <div style="background:rgba(0,0,0,0.08);border-radius:6px;padding:0.75rem;font-size:0.78rem;font-family:monospace;line-height:1.9">
                @foreach($qbLabels as $label)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem">
                        <span>{{ $label }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ addslashes($label) }}')"
                                style="background:none;border:none;cursor:pointer;color:var(--gold);font-size:0.7rem;padding:0">copy</button>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card" style="font-size:0.82rem;color:var(--text-muted)">
            <div style="font-weight:600;color:var(--text);margin-bottom:0.4rem">QuickBooks Types</div>
            @if($tenant->qb_enabled ?? false)
                Connect QuickBooks to see your customer types here for easy copy-paste mapping.
            @else
                Enable QuickBooks in Settings to see your customer types here.
            @endif
        </div>
        @endif
    </div>

</div>
@endsection
