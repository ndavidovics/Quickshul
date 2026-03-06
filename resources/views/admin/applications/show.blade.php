@extends('layouts.admin')
@section('title', 'Application — '.($application->data['family_name'] ?? ''))

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline btn-sm">← Applications</a>
    <h1 class="page-title" style="margin-bottom:0">{{ $application->data['family_name'] ?? 'Application #'.$application->id }}</h1>
    @if($application->status === 'pending')
        <span class="badge badge-blue" style="font-size:0.75rem;padding:0.3rem 0.75rem">Pending Review</span>
    @elseif($application->status === 'approved')
        <span class="badge badge-green" style="font-size:0.75rem;padding:0.3rem 0.75rem">Approved</span>
    @else
        <span class="badge badge-red" style="font-size:0.75rem;padding:0.3rem 0.75rem">Rejected</span>
    @endif
    @if($application->family)
        <a href="{{ route('admin.members.show', $application->family_id) }}" class="btn btn-primary btn-sm">View Member Record</a>
    @endif
</div>

<div class="grid-2">
    {{-- Application details --}}
    <div class="card">
        <div class="card-title">Application Details</div>
        @php $d = $application->data; @endphp
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted);width:40%">Submitted</td>
                <td style="padding:0.5rem 0;font-weight:500">{{ $application->created_at->format('F j, Y g:i a') }}</td>
            </tr>
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Membership Type</td>
                <td style="padding:0.5rem 0;font-weight:500">{{ $application->membershipLabel() }}</td>
            </tr>
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Phone</td>
                <td style="padding:0.5rem 0">{{ $d['phone'] ?? '—' }}</td>
            </tr>
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Address</td>
                <td style="padding:0.5rem 0">
                    @if($d['address'] ?? null)
                        {{ $d['address'] }}<br>
                        {{ $d['city'] ?? '' }}{{ isset($d['state']) ? ', '.$d['state'] : '' }} {{ $d['zip'] ?? '' }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Review status --}}
    <div class="card">
        <div class="card-title">Review Status</div>
        @if($application->status !== 'pending')
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse">
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted);width:40%">Decision</td>
                <td style="padding:0.5rem 0;font-weight:500;text-transform:capitalize">{{ $application->status }}</td>
            </tr>
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Reviewed By</td>
                <td style="padding:0.5rem 0">{{ $application->reviewer?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Reviewed At</td>
                <td style="padding:0.5rem 0">{{ $application->reviewed_at?->format('F j, Y g:i a') ?? '—' }}</td>
            </tr>
            @if($application->admin_notes)
            <tr>
                <td style="padding:0.5rem 0;color:var(--text-muted)">Notes</td>
                <td style="padding:0.5rem 0">{{ $application->admin_notes }}</td>
            </tr>
            @endif
        </table>
        @else
        <p class="text-muted text-sm">Not yet reviewed.</p>
        @endif
    </div>
</div>

{{-- Emails --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Email Addresses</div>
    @forelse($d['emails'] ?? [] as $email)
    <div style="padding:0.4rem 0;border-bottom:1px solid var(--border-dim);font-size:0.875rem">{{ $email }}</div>
    @empty
    <p class="text-muted text-sm">None provided.</p>
    @endforelse
</div>

{{-- Family Members --}}
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Family Members</div>
    <table class="table">
        <thead><tr><th>Name</th><th>Hebrew Name</th><th>Gender</th><th>Role</th><th>Date of Birth</th></tr></thead>
        <tbody>
            @forelse($d['members'] ?? [] as $m)
            <tr>
                <td style="font-weight:500">{{ $m['first_name'] }} {{ $m['last_name'] }}</td>
                <td style="direction:rtl;font-family:serif;font-size:0.95rem">{{ $m['hebrew_name'] ?? '—' }}</td>
                <td><span class="badge badge-muted">{{ ucfirst($m['gender']) }}</span></td>
                <td><span class="badge badge-muted">{{ ucfirst($m['role']) }}</span></td>
                <td class="text-muted text-sm">{{ isset($m['date_of_birth']) && $m['date_of_birth'] ? \Carbon\Carbon::parse($m['date_of_birth'])->format('M j, Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-muted" style="text-align:center;padding:1rem">No members listed.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($d['notes'] ?? null)
<div class="card" style="margin-top:1.25rem">
    <div class="card-title">Applicant Notes</div>
    <p style="font-size:0.875rem;line-height:1.6">{{ $d['notes'] }}</p>
</div>
@endif

{{-- Approve / Reject --}}
@if($application->isPending())
<div class="grid-2" style="margin-top:1.25rem">
    {{-- Approve --}}
    <div class="card" style="border-color:rgba(46,204,113,0.25)">
        <div class="card-title" style="color:#6fe8a2">Approve Application</div>
        <p class="text-sm text-muted" style="margin-bottom:1rem">
            This will create a family record, member accounts, login access, and push to QuickBooks.
        </p>
        <form method="POST" action="{{ route('admin.applications.approve', $application->id) }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label">Admin Notes <span class="text-muted" style="font-weight:400">(optional)</span></label>
                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes...">{{ old('admin_notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-gold" onclick="return confirm('Approve this application and create the member record?')">
                ✓ Approve &amp; Create Member
            </button>
        </form>
    </div>

    {{-- Reject --}}
    <div class="card" style="border-color:rgba(231,76,60,0.2)">
        <div class="card-title" style="color:#f08080">Reject Application</div>
        <p class="text-sm text-muted" style="margin-bottom:1rem">
            The applicant will not be notified automatically.
        </p>
        <form method="POST" action="{{ route('admin.applications.reject', $application->id) }}">
            @csrf
            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label">Reason / Notes <span class="text-muted" style="font-weight:400">(optional)</span></label>
                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Reason for rejection...">{{ old('admin_notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this application?')">
                ✕ Reject Application
            </button>
        </form>
    </div>
</div>
@endif
@endsection
