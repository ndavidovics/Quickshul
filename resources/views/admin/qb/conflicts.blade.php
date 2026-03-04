@extends('layouts.admin')
@section('title', 'QB Conflicts')

@section('content')
<div class="flex items-center gap-3" style="margin-bottom:1.5rem">
    <a href="{{ route('admin.qb') }}" class="btn btn-outline btn-sm">← QB Dashboard</a>
    <h1 class="page-title" style="margin-bottom:0">Data Conflicts</h1>
</div>

@if(session('success'))
<div style="background:rgba(100,200,120,0.12);border:1px solid rgba(100,200,120,0.4);border-radius:6px;padding:0.875rem 1rem;margin-bottom:1.25rem;color:#7ecf8e;font-size:0.875rem">
    {{ session('success') }}
</div>
@endif

<p class="text-muted text-sm" style="margin-bottom:1.25rem">
    These fields were changed in both the portal and QuickBooks since the last sync. Choose which value to keep for each conflict.
</p>

@if($conflicts->isEmpty())
<div class="card" style="text-align:center;padding:3rem">
    <div style="font-size:2rem;margin-bottom:0.75rem">✓</div>
    <div style="font-weight:600;margin-bottom:0.3rem">No Unresolved Conflicts</div>
    <div class="text-sm text-muted">All data conflicts have been resolved.</div>
</div>
@else
<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Entity</th>
                <th>Field</th>
                <th>Portal Value</th>
                <th>QuickBooks Value</th>
                <th>Detected</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($conflicts as $conflict)
            <tr>
                <td>
                    <div style="font-weight:500;font-size:0.875rem">{{ $conflict->entity_type }}</div>
                    <div class="text-sm text-muted" style="font-family:monospace">ID {{ $conflict->entity_id }}</div>
                </td>
                <td>
                    <span class="badge badge-muted" style="font-family:monospace;font-size:0.72rem">{{ $conflict->field }}</span>
                </td>
                <td class="text-sm" style="max-width:180px;word-break:break-word">
                    <div style="background:rgba(201,168,76,0.1);border:1px solid rgba(201,168,76,0.25);border-radius:4px;padding:0.3rem 0.5rem">
                        {{ $conflict->portal_value ?? '(empty)' }}
                    </div>
                </td>
                <td class="text-sm" style="max-width:180px;word-break:break-word">
                    <div style="background:rgba(44,160,28,0.1);border:1px solid rgba(44,160,28,0.25);border-radius:4px;padding:0.3rem 0.5rem">
                        {{ $conflict->qb_value ?? '(empty)' }}
                    </div>
                </td>
                <td class="text-sm text-muted">{{ $conflict->created_at->format('M j, Y') }}</td>
                <td>
                    <div style="display:flex;gap:0.4rem;flex-direction:column">
                        <form method="POST" action="{{ route('admin.qb.resolve', $conflict->id) }}" style="display:inline">
                            @csrf
                            <input type="hidden" name="resolution" value="portal">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%">Keep Portal</button>
                        </form>
                        <form method="POST" action="{{ route('admin.qb.resolve', $conflict->id) }}" style="display:inline">
                            @csrf
                            <input type="hidden" name="resolution" value="qb">
                            <button type="submit" class="btn btn-outline btn-sm" style="width:100%">Use QB</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $conflicts->links() }}</div>
</div>
@endif
@endsection
