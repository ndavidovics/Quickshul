@extends('layouts.admin')
@section('title', $event ? 'Edit Event' : 'New Event')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $event ? 'Edit Event' : 'New Event' }}</h1>
        <p class="page-subtitle">{{ $event ? 'Update event details and ticket types' : 'Create a public payment page for your event' }}</p>
    </div>
    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">← Back</a>
</div>

<form method="POST" action="{{ $event ? route('admin.events.update', $event->id) : route('admin.events.store') }}" id="event-form">
    @csrf
    @if($event) @method('PUT') @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0;padding-left:1.2rem">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
        <div class="card" style="grid-column:1/-1">
            <h3 class="card-title">Event Details</h3>
            <div class="form-grid">
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Event Name *</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $event?->name) }}" required placeholder="e.g. Purim Dinner 2026">
                </div>
                <div class="form-group">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-input" value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Family Maximum ($)</label>
                    <input type="number" name="family_max" class="form-input" step="0.01" min="0"
                        value="{{ old('family_max', $event?->family_max) }}"
                        placeholder="e.g. 30 (leave blank for no cap)">
                    <div class="form-hint">Maximum total charge per family, regardless of tickets selected.</div>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Optional details about the event">{{ old('description', $event?->description) }}</textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label class="form-label">QuickBooks Item ID</label>
                    <input type="text" name="qb_item_id" class="form-input"
                        value="{{ old('qb_item_id', $event?->qb_item_id) }}"
                        placeholder="QB product/service ID (for logged-in member payments)">
                    <div class="form-hint">When set and QuickBooks is connected, logged-in member payments will be posted to this product/service item.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem">
            <h3 class="card-title" style="margin:0">Ticket Types *</h3>
            <button type="button" onclick="addTicket()" class="btn btn-sm btn-secondary">+ Add Ticket Type</button>
        </div>

        <div id="tickets-container">
            @php
                $existingTickets = old('tickets', $event ? $event->ticketTypes->map(fn($t) => ['name' => $t->name, 'price' => $t->price])->toArray() : [['name' => '', 'price' => '']]);
            @endphp
            @foreach($existingTickets as $i => $ticket)
            <div class="ticket-row" data-index="{{ $i }}">
                <div style="display:grid;grid-template-columns:1fr auto auto;gap:0.75rem;align-items:center;margin-bottom:0.75rem">
                    <input type="text" name="tickets[{{ $i }}][name]" class="form-input" placeholder="Ticket name (e.g. Adult, Child, Table)" value="{{ $ticket['name'] }}" required>
                    <div style="display:flex;align-items:center;gap:0.4rem">
                        <span style="color:var(--text-muted)">$</span>
                        <input type="number" name="tickets[{{ $i }}][price]" class="form-input" style="width:100px" placeholder="0.00" step="0.01" min="0" value="{{ $ticket['price'] }}" required>
                    </div>
                    <button type="button" onclick="removeTicket(this)" style="background:none;border:none;color:var(--error);cursor:pointer;font-size:1.1rem;padding:0.25rem" title="Remove">✕</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="display:flex;gap:1rem">
        <button type="submit" class="btn btn-primary">{{ $event ? 'Save Changes' : 'Create Event' }}</button>
        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Cancel</a>
        @if($event)
        <div style="margin-left:auto">
            <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}" onsubmit="return confirm('Delete this event? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn" style="background:rgba(231,76,60,0.15);color:#e74c3c;border:1px solid rgba(231,76,60,0.3)">Delete Event</button>
            </form>
        </div>
        @endif
    </div>
</form>

<script>
let ticketIndex = {{ count($existingTickets) }};

function addTicket() {
    const container = document.getElementById('tickets-container');
    const row = document.createElement('div');
    row.className = 'ticket-row';
    row.dataset.index = ticketIndex;
    row.innerHTML = `
        <div style="display:grid;grid-template-columns:1fr auto auto;gap:0.75rem;align-items:center;margin-bottom:0.75rem">
            <input type="text" name="tickets[${ticketIndex}][name]" class="form-input" placeholder="Ticket name (e.g. Adult, Child, Table)" required>
            <div style="display:flex;align-items:center;gap:0.4rem">
                <span style="color:var(--text-muted)">$</span>
                <input type="number" name="tickets[${ticketIndex}][price]" class="form-input" style="width:100px" placeholder="0.00" step="0.01" min="0" required>
            </div>
            <button type="button" onclick="removeTicket(this)" style="background:none;border:none;color:var(--error);cursor:pointer;font-size:1.1rem;padding:0.25rem" title="Remove">✕</button>
        </div>
    `;
    container.appendChild(row);
    ticketIndex++;
    row.querySelector('input[type=text]').focus();
}

function removeTicket(btn) {
    const rows = document.querySelectorAll('.ticket-row');
    if (rows.length <= 1) {
        alert('At least one ticket type is required.');
        return;
    }
    btn.closest('.ticket-row').remove();
}
</script>
@endsection
