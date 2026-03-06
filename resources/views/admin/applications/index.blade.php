@extends('layouts.admin')
@section('title', 'Membership Applications')

@section('content')
<div class="flex items-center justify-between" style="margin-bottom:1.5rem">
    <div>
        <h1 class="page-title">Membership Applications</h1>
        <p class="page-subtitle" style="margin-bottom:0">{{ $applications->total() }} {{ $status === 'all' ? 'total' : $status }} application{{ $applications->total() !== 1 ? 's' : '' }}</p>
    </div>
</div>

{{-- Status tabs --}}
<div style="display:flex;gap:0.4rem;margin-bottom:1.25rem;flex-wrap:wrap">
    @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $s => $label)
    <a href="{{ route('admin.applications.index') }}?status={{ $s }}"
       class="btn btn-sm {{ $status === $s ? 'btn-primary' : 'btn-outline' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Family Name</th>
                <th>Membership</th>
                <th>Primary Email</th>
                <th>Submitted</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($applications as $app)
            <tr>
                <td class="text-muted text-sm">{{ $app->id }}</td>
                <td style="font-weight:500">{{ $app->data['family_name'] ?? '—' }}</td>
                <td>
                    <span class="badge badge-gold" style="font-size:0.68rem">
                        {{ match($app->membership_type) {
                            'full_family'     => 'Full Family',
                            'associate'       => 'Associate',
                            'single'          => 'Single',
                            'first_year_free' => 'Complimentary',
                            default           => $app->membership_type,
                        } }}
                    </span>
                </td>
                <td class="text-sm text-muted">{{ $app->data['emails'][0] ?? '—' }}</td>
                <td class="text-sm text-muted">{{ $app->created_at->format('M j, Y') }}</td>
                <td>
                    @if($app->status === 'pending')
                        <span class="badge badge-blue">Pending</span>
                    @elseif($app->status === 'approved')
                        <span class="badge badge-green">Approved</span>
                    @else
                        <span class="badge badge-red">Rejected</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.applications.show', $app->id) }}" class="btn btn-outline btn-sm">Review</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)">No {{ $status !== 'all' ? $status : '' }} applications.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $applications->links() }}</div>
</div>
@endsection
