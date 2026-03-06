@extends('layouts.app')
@section('title', 'Financial')

@section('content')
<h1 class="page-title">Financial</h1>
<p class="page-subtitle">Payment history and balance</p>

@if(!$family)
    <div class="card"><p class="text-muted">No family account linked.</p></div>
@else

{{-- Balance summary --}}
<div class="grid-2" style="margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value {{ $family->outstanding_balance > 0 ? '' : 'gold' }}">${{ number_format($family->outstanding_balance, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Donated (Past 12 Months)</div>
        <div class="stat-value gold">${{ number_format($paidPast12Months, 2) }}</div>
    </div>
</div>

{{-- Pay Now --}}
@if($family->outstanding_balance > 0)
<div class="card" style="margin-bottom:1.5rem;border-color:rgba(201,168,76,0.4)">
    <div class="card-title">Pay Your Balance</div>
    <form method="POST" action="{{ route('financial.pay') }}" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap">
        @csrf
        <div class="form-group" style="flex:1;min-width:200px;margin-bottom:0">
            <label class="form-label">Amount to Pay</label>
            <input type="number" name="amount" class="form-control"
                   value="{{ $family->outstanding_balance }}"
                   min="1" max="{{ $family->outstanding_balance }}"
                   step="0.01" required>
        </div>
        <button type="submit" class="btn btn-gold" style="height:40px">
            <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png" alt="PayPal" height="16" style="vertical-align:middle">
            &nbsp;Pay Now
        </button>
    </form>
</div>
@else
<div class="alert alert-success" style="margin-bottom:1.5rem">✓ Your account is current — no outstanding balance.</div>
@endif

{{-- Pledge / Invoice History --}}
<div class="card" style="margin-bottom:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Pledge History</div>
        <a href="{{ route('financial.export.pledges') }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <div id="pledges-body">
        @include('member.financial._pledges')
    </div>
</div>

{{-- Payment history --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem">
        <div class="card-title" style="margin-bottom:0">Payment History</div>
        <a href="{{ route('financial.export.payments') }}" class="btn btn-outline btn-sm" title="Export CSV">⬇ CSV</a>
    </div>
    <div id="payments-body">
        @include('member.financial._payments')
    </div>
</div>

@section('scripts')
<script>
(function () {
    function ajaxPaginate(containerId, endpoint) {
        var container = document.getElementById(containerId);
        if (!container) return;

        container.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            e.preventDefault();

            var url = new URL(link.href);
            // Accept ?page=, ?lp=, ?pp=, or any numeric-looking query param
            var page = url.searchParams.get('page')
                    || url.searchParams.get('lp')
                    || url.searchParams.get('pp')
                    || '1';
            url.searchParams.forEach(function (v) { if (/^\d+$/.test(v)) page = v; });

            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            fetch(endpoint + '?page=' + page, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                container.innerHTML = data.html;
                container.style.opacity = '';
                container.style.pointerEvents = '';
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            })
            .catch(function () {
                container.style.opacity = '';
                container.style.pointerEvents = '';
            });
        });
    }

    ajaxPaginate('pledges-body',  '{{ route('financial.pledges') }}');
    ajaxPaginate('payments-body', '{{ route('financial.payments') }}');
})();
</script>
@endsection
@endif
@endsection
