@extends('layouts.admin')
@section('title', 'Event Payments')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Event Payments</h1>
        <p class="page-subtitle">Create payment portals for specific events</p>
    </div>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">+ New Event</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($events->isEmpty())
    <div class="empty-state">
        <div style="font-size:3rem;margin-bottom:1rem">🎟</div>
        <h3>No events yet</h3>
        <p>Create your first event to generate a public payment page for it.</p>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary" style="margin-top:1rem">Create Event</a>
    </div>
@else
    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Payments</th>
                    <th>Revenue</th>
                    <th>Payment URL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                <tr>
                    <td>
                        <strong>{{ $event->name }}</strong>
                        @if($event->family_max)
                            <br><span style="font-size:0.75rem;color:var(--text-muted)">Family max: ${{ number_format($event->family_max, 2) }}</span>
                        @endif
                    </td>
                    <td>{{ $event->event_date ? $event->event_date->format('M j, Y') : '—' }}</td>
                    <td>
                        @if($event->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-muted">Closed</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.events.payments', $event->id) }}">
                            {{ $event->completed_payments_count }}
                        </a>
                    </td>
                    <td>${{ number_format($event->revenue ?? 0, 2) }}</td>
                    <td>
                        @php
                            $tenant = app('tenant');
                            $domain = config('app.root_domain', 'quickshul.com');
                            $url = 'https://' . $tenant->slug . '.' . $domain . '/events/' . $tenant->slug . '/' . $event->slug;
                        @endphp
                        <div style="display:flex;align-items:center;gap:0.5rem">
                            <a href="{{ $url }}" target="_blank" style="font-size:0.75rem;color:var(--gold);word-break:break-all">
                                {{ $tenant->slug }}.{{ $domain }}/events/…/{{ $event->slug }}
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ $url }}').then(()=>this.textContent='✓').catch(()=>{})"
                                style="background:none;border:1px solid var(--border);color:var(--text-muted);padding:0.15rem 0.4rem;border-radius:4px;cursor:pointer;font-size:0.7rem;white-space:nowrap">
                                Copy
                            </button>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.5rem;justify-content:flex-end">
                            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                            @if($event->status === 'active')
                                <form method="POST" action="{{ route('admin.events.close', $event->id) }}" onsubmit="return confirm('Close this event? No new payments will be accepted.')">
                                    @csrf
                                    <button class="btn btn-sm" style="background:rgba(231,76,60,0.15);color:#e74c3c;border:1px solid rgba(231,76,60,0.3)">Close</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.events.reopen', $event->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-secondary">Reopen</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
