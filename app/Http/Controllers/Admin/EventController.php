<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventTicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount('completedPayments')
            ->withSum('completedPayments as revenue', 'total_amount')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_date'  => 'nullable|date',
            'family_max'  => 'nullable|numeric|min:0',
            'qb_item_id'  => 'nullable|string|max:100',
            'tickets'     => 'required|array|min:1',
            'tickets.*.name'  => 'required|string|max:100',
            'tickets.*.price' => 'required|numeric|min:0',
        ]);

        $tenant = app('tenant');
        $slug   = Event::generateSlug($data['name'], $tenant->id);

        $event = Event::create([
            'tenant_id'   => $tenant->id,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'event_date'  => $data['event_date'] ?? null,
            'family_max'  => $data['family_max'] ?? null,
            'qb_item_id'  => $data['qb_item_id'] ?? null,
            'status'      => 'active',
        ]);

        foreach ($data['tickets'] as $i => $ticket) {
            EventTicketType::create([
                'tenant_id'  => $tenant->id,
                'event_id'   => $event->id,
                'name'       => $ticket['name'],
                'price'      => $ticket['price'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event "' . $event->name . '" created.');
    }

    public function edit(int $id)
    {
        $event = Event::with('ticketTypes')->findOrFail($id);
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, int $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_date'  => 'nullable|date',
            'family_max'  => 'nullable|numeric|min:0',
            'qb_item_id'  => 'nullable|string|max:100',
            'tickets'     => 'required|array|min:1',
            'tickets.*.name'  => 'required|string|max:100',
            'tickets.*.price' => 'required|numeric|min:0',
        ]);

        $event->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'event_date'  => $data['event_date'] ?? null,
            'family_max'  => $data['family_max'] ?? null,
            'qb_item_id'  => $data['qb_item_id'] ?? null,
        ]);

        // Replace ticket types
        $event->ticketTypes()->delete();
        $tenant = app('tenant');
        foreach ($data['tickets'] as $i => $ticket) {
            EventTicketType::create([
                'tenant_id'  => $tenant->id,
                'event_id'   => $event->id,
                'name'       => $ticket['name'],
                'price'      => $ticket['price'],
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated.');
    }

    public function close(int $id)
    {
        Event::findOrFail($id)->update(['status' => 'closed']);
        return back()->with('success', 'Event closed.');
    }

    public function reopen(int $id)
    {
        Event::findOrFail($id)->update(['status' => 'active']);
        return back()->with('success', 'Event reopened.');
    }

    public function destroy(int $id)
    {
        Event::findOrFail($id)->delete();
        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted.');
    }

    public function payments(int $id)
    {
        $event    = Event::with('ticketTypes')->findOrFail($id);
        $payments = $event->completedPayments()
            ->with('family')
            ->orderByDesc('created_at')
            ->get();

        $totalRevenue = $payments->sum('total_amount');

        return view('admin.events.payments', compact('event', 'payments', 'totalRevenue'));
    }
}
