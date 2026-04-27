<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} — {{ app('tenant')->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --navy: #1a2d5a; --navy-dark: #111d3c; --bg: #0f1a2e; --bg-card: #162240;
            --gold: #c9a84c; --gold-light: #e0c06a; --text: #e8e4dc; --text-muted: #8899bb;
            --border: rgba(201,168,76,0.2); --success: #2ecc71; --error: #e74c3c;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
        .header { background: var(--navy-dark); border-bottom: 1px solid var(--border); padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .header-brand { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--gold); text-decoration: none; }
        .container { max-width: 600px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; }
        .event-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--gold); margin-bottom: 0.5rem; }
        .event-meta { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; }
        .event-desc { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.5rem; }
        .section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); margin-bottom: 0.75rem; }
        .ticket-row { display: grid; grid-template-columns: 1fr auto auto; align-items: center; gap: 1rem; padding: 0.875rem 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .ticket-row:last-child { border-bottom: none; }
        .ticket-name { font-weight: 500; }
        .ticket-price { color: var(--gold); font-size: 0.9rem; white-space: nowrap; }
        .qty-control { display: flex; align-items: center; gap: 0.5rem; }
        .qty-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border); background: none; color: var(--text); cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
        .qty-btn:hover { border-color: var(--gold); color: var(--gold); }
        .qty-display { min-width: 24px; text-align: center; font-weight: 600; }
        .form-label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem; }
        .form-input { width: 100%; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 0.65rem 0.875rem; color: var(--text); font-family: 'Inter', sans-serif; font-size: 0.9rem; outline: none; transition: border-color 0.15s; }
        .form-input:focus { border-color: var(--gold); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
        .total-row { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-top: 1px solid var(--border); margin-top: 0.5rem; }
        .total-label { font-size: 0.85rem; color: var(--text-muted); }
        .total-amount { font-size: 1.4rem; font-weight: 700; color: var(--gold); }
        .family-max-note { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
        #paypal-button-container { margin-top: 1rem; }
        .alert { padding: 0.875rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
        .alert-error { background: rgba(231,76,60,0.15); border: 1px solid rgba(231,76,60,0.3); color: #f1948a; }
        .alert-success { background: rgba(46,204,113,0.15); border: 1px solid rgba(46,204,113,0.3); color: var(--success); }
        .success-state { text-align: center; padding: 2rem; }
        .success-icon { font-size: 3rem; margin-bottom: 1rem; }
        .success-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--gold); margin-bottom: 0.5rem; }
        .login-hint { background: rgba(201,168,76,0.08); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.8rem; color: var(--text-muted); }
        .login-hint a { color: var(--gold); }
        @media(max-width:480px) { .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="header">
    <a href="/" class="header-brand">{{ app('tenant')->name }}</a>
    @auth
        <span style="font-size:0.8rem;color:var(--text-muted)">{{ auth()->user()->name }}</span>
    @else
        <a href="{{ route('login') }}" style="font-size:0.8rem;color:var(--gold);text-decoration:none">Sign in</a>
    @endauth
</div>

<div class="container">
    <div class="card">
        <div class="event-title">{{ $event->name }}</div>
        <div class="event-meta">
            @if($event->event_date)
                📅 {{ $event->event_date->format('l, F j, Y') }} &nbsp;·&nbsp;
            @endif
            {{ app('tenant')->name }}
        </div>
        @if($event->description)
            <div class="event-desc">{{ $event->description }}</div>
        @endif

        <div class="section-title">Select Tickets</div>
        <div id="tickets-list">
            @foreach($event->ticketTypes as $type)
            <div class="ticket-row">
                <div>
                    <div class="ticket-name">{{ $type->name }}</div>
                    <div class="ticket-price">${{ number_format($type->price, 2) }} each</div>
                </div>
                <div></div>
                <div class="qty-control">
                    <button type="button" class="qty-btn" onclick="changeQty({{ $type->id }}, -1)">−</button>
                    <span class="qty-display" id="qty-{{ $type->id }}">0</span>
                    <button type="button" class="qty-btn" onclick="changeQty({{ $type->id }}, 1)">+</button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="total-row">
            <div>
                <div class="total-label">Total</div>
                @if($event->family_max)
                    <div class="family-max-note">Family max: ${{ number_format($event->family_max, 2) }}</div>
                @endif
            </div>
            <div class="total-amount" id="total-display">$0.00</div>
        </div>
    </div>

    <div class="card" id="payer-section" style="display:none">
        <div class="section-title" style="margin-bottom:1rem">Your Information</div>

        @guest
        <div class="login-hint">
            Already a member? <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}">Sign in</a> to auto-fill your information.
        </div>
        @endguest

        <div class="form-grid">
            <div>
                <label class="form-label">Full Name *</label>
                <input type="text" id="payer-name" class="form-input" value="{{ $prefill['name'] ?? '' }}" required placeholder="Your name">
            </div>
            <div>
                <label class="form-label">Email Address *</label>
                <input type="email" id="payer-email" class="form-input" value="{{ $prefill['email'] ?? '' }}" required placeholder="you@example.com">
            </div>
        </div>

        <div id="error-msg" class="alert alert-error" style="display:none"></div>
        <div id="paypal-button-container"></div>
    </div>

    <div class="card success-state" id="success-section" style="display:none">
        <div class="success-icon">✅</div>
        <div class="success-title">Payment Successful!</div>
        <p style="color:var(--text-muted);margin-top:0.5rem" id="success-msg">Thank you for your purchase.</p>
    </div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD"></script>
<script>
const ticketPrices = {
    @foreach($event->ticketTypes as $type)
    {{ $type->id }}: {{ (float)$type->price }},
    @endforeach
};
const familyMax = {{ $event->family_max ? (float)$event->family_max : 'null' }};
let quantities = {};

function changeQty(id, delta) {
    quantities[id] = Math.max(0, (quantities[id] || 0) + delta);
    document.getElementById('qty-' + id).textContent = quantities[id];
    updateTotal();
}

function updateTotal() {
    let subtotal = 0;
    for (const [id, qty] of Object.entries(quantities)) {
        if (qty > 0 && ticketPrices[id] !== undefined) {
            subtotal += ticketPrices[id] * qty;
        }
    }
    let total = subtotal;
    if (familyMax !== null && total > familyMax) total = familyMax;

    document.getElementById('total-display').textContent = '$' + total.toFixed(2);

    const hasTickets = subtotal > 0;
    document.getElementById('payer-section').style.display = hasTickets ? 'block' : 'none';
    if (!hasTickets) {
        document.getElementById('paypal-button-container').innerHTML = '';
    } else {
        renderPayPal(total);
    }
}

let ppRendered = false;
let ppTotal = 0;

function renderPayPal(total) {
    if (ppRendered && ppTotal === total) return;
    ppRendered = false;
    ppTotal = total;
    document.getElementById('paypal-button-container').innerHTML = '';

    paypal.Buttons({
        createOrder: async function() {
            const name  = document.getElementById('payer-name').value.trim();
            const email = document.getElementById('payer-email').value.trim();

            if (!name || !email) {
                document.getElementById('error-msg').textContent = 'Please enter your name and email.';
                document.getElementById('error-msg').style.display = 'block';
                throw new Error('Missing fields');
            }
            document.getElementById('error-msg').style.display = 'none';

            const resp = await fetch('{{ route('event.pay.create-order', [app('tenant')->slug, $event->slug]) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    payer_name: name,
                    payer_email: email,
                    quantities: quantities,
                }),
            });
            const data = await resp.json();
            if (!resp.ok) throw new Error(data.error || 'Order creation failed');
            return data.id;
        },
        onApprove: async function(data) {
            const resp = await fetch('{{ route('event.pay.capture', [app('tenant')->slug, $event->slug]) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ order_id: data.orderID }),
            });
            const result = await resp.json();
            if (result.success) {
                document.querySelectorAll('.card').forEach(c => c.style.display = 'none');
                const s = document.getElementById('success-section');
                s.style.display = 'block';
                document.getElementById('success-msg').textContent = result.message;
            } else {
                document.getElementById('error-msg').textContent = result.error || 'Capture failed.';
                document.getElementById('error-msg').style.display = 'block';
            }
        },
        onError: function(err) {
            console.error(err);
            document.getElementById('error-msg').textContent = 'PayPal encountered an error. Please try again.';
            document.getElementById('error-msg').style.display = 'block';
        },
        style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'pay' },
    }).render('#paypal-button-container').then(() => { ppRendered = true; });
}
</script>
</body>
</html>
