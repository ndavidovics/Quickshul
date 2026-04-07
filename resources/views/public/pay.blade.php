<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pay Online — {{ $tenant->name ?? config('app.name') }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0ede8;color:#1a2d5a;min-height:100vh}
.header{background:#1a2d5a;padding:1.25rem 1.5rem;text-align:center}
.header-logo{color:#c9a84c;font-size:1.35rem;font-weight:700;letter-spacing:0.02em;font-family:Georgia,serif}
.header-sub{color:rgba(255,255,255,0.6);font-size:0.78rem;margin-top:0.25rem;letter-spacing:0.08em;text-transform:uppercase}
.container{max-width:640px;margin:2rem auto;padding:0 1rem 3rem}
.card{background:#fff;border-radius:10px;padding:1.75rem;box-shadow:0 2px 16px rgba(26,45,90,0.08);margin-bottom:1.25rem}
.greeting{font-family:Georgia,serif;font-size:1.4rem;color:#1a2d5a;margin-bottom:0.4rem}
.subtitle{color:#666;font-size:0.9rem;line-height:1.6}
.section-title{font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#c9a84c;margin-bottom:1rem}
.pledge-row{border:1px solid #e8e4dc;border-radius:8px;padding:1rem;margin-bottom:0.75rem;transition:border-color 0.2s,opacity 0.2s}
.pledge-row:focus-within{border-color:#c9a84c}
.pledge-row.removed{opacity:0.45;background:#fafafa}
.pledge-row.removed .pledge-input-row{display:none}
.pledge-row-header{display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem}
.pledge-desc{font-weight:600;color:#1a2d5a;font-size:0.95rem;margin-bottom:0.2rem}
.pledge-meta{font-size:0.78rem;color:#888;margin-bottom:0.75rem}
.pledge-input-row{display:flex;align-items:center;gap:0.75rem}
.pledge-input-row label{font-size:0.82rem;color:#555;white-space:nowrap}
.amount-input{border:1px solid #d0ccc4;border-radius:6px;padding:0.45rem 0.6rem;font-size:1rem;width:120px;color:#1a2d5a;font-weight:600}
.amount-input:focus{outline:none;border-color:#c9a84c}
.full-btn{font-size:0.78rem;color:#c9a84c;border:none;background:none;cursor:pointer;text-decoration:underline;padding:0}
.remove-btn{background:none;border:none;color:#bbb;font-size:1.3rem;line-height:1;cursor:pointer;padding:0.1rem 0.3rem;border-radius:4px;flex-shrink:0;margin-top:-0.1rem}
.remove-btn:hover{color:#e53e3e;background:#fff0f0}
.restore-btn{font-size:0.78rem;color:#c9a84c;border:none;background:none;cursor:pointer;text-decoration:underline;padding:0;margin-top:0.5rem;display:block}
.total-row{display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-top:2px solid #1a2d5a;margin-top:0.5rem;margin-bottom:1.25rem}
.total-label{font-weight:700;color:#1a2d5a}
.total-amount{font-size:1.4rem;font-weight:700;color:#c9a84c}
.method-tabs{display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem}
.method-tab{padding:0.45rem 1.1rem;border-radius:6px;border:1px solid #d0ccc4;background:#fff;color:#555;font-size:0.85rem;cursor:pointer;font-weight:500;transition:background 0.15s,color 0.15s}
.method-tab.active{background:#1a2d5a;color:#c9a84c;border-color:#1a2d5a}
.method-content{display:none}
.method-content.active{display:block}
.apple-pay-button{-webkit-appearance:-apple-pay-button;-apple-pay-button-type:plain;-apple-pay-button-style:black;width:100%;height:48px;border-radius:8px;border:none;cursor:pointer;display:block}
#google-pay-container > button{width:100% !important;border-radius:8px !important}
.success-box{background:#f0fff4;border:1px solid #68d391;border-radius:8px;padding:1.25rem;text-align:center}
.success-title{font-family:Georgia,serif;font-size:1.25rem;color:#276749;margin-bottom:0.5rem}
.success-sub{color:#2f855a;font-size:0.875rem}
.error-box{background:#fff5f5;border:1px solid #fc8181;border-radius:8px;padding:1rem;color:#c53030;font-size:0.875rem;margin-bottom:1rem;display:none}
.footer{text-align:center;color:#999;font-size:0.75rem;margin-top:2rem;line-height:1.8}
.no-balance{text-align:center;color:#888;padding:1.5rem 0;font-size:0.9rem}
.fee-row{display:flex;align-items:center;gap:0.6rem;padding:0.6rem 0;border-top:1px solid #e8e4dc;margin-top:0.25rem}
.fee-row label{font-size:0.875rem;color:#555;cursor:pointer;user-select:none;display:flex;align-items:center;gap:0.5rem;flex:1}
.fee-row input[type=checkbox]{width:16px;height:16px;accent-color:#c9a84c;cursor:pointer;flex-shrink:0}
.fee-note{font-size:0.78rem;color:#888;margin-left:auto}
</style>
</head>
<body>

<div class="header">
    <div class="header-logo">{{ $tenant->name ?? config('app.name') }}</div>
    <div class="header-sub">{{ $tenant->tagline ?? '' }}</div>
</div>

<div class="container">

    <div class="card">
        <div class="greeting">Shalom, {{ $family->name }}</div>
        <p class="subtitle" style="margin-top:0.5rem">
            Thank you for your commitment to our community. Below are your current open pledges.
            You may pay the full amount or adjust each pledge as you see fit.
        </p>
    </div>

    @if($openPledges->isEmpty())
    <div class="card">
        <div class="no-balance">
            You have no outstanding balances at this time.<br>
            <span style="font-size:0.82rem;margin-top:0.4rem;display:block">Thank you for your continued support of {{ $tenant->name ?? config('app.name') }}.</span>
        </div>
    </div>
    @else
    <div class="card" id="payment-card">
        <div class="section-title">Outstanding Pledges</div>

        <div class="error-box" id="error-box"></div>

        @foreach($openPledges as $pledge)
        <div class="pledge-row" data-balance="{{ number_format((float)$pledge->balance, 2, '.', '') }}">
            <div class="pledge-row-header">
                <div style="flex:1">
                    <div class="pledge-desc">{{ $pledge->description ?: 'Pledge' }}</div>
                    <div class="pledge-meta">
                        Invoice {{ $pledge->invoice_date->format('M j, Y') }}
                        &nbsp;&middot;&nbsp;
                        Total pledged: ${{ number_format($pledge->amount, 2) }}
                        &nbsp;&middot;&nbsp;
                        Balance due: <strong style="color:#1a2d5a">${{ number_format($pledge->balance, 2) }}</strong>
                    </div>
                </div>
                <button type="button" class="remove-btn" onclick="removePledge(this)" title="Remove from payment">×</button>
            </div>
            <div class="pledge-input-row">
                <label>Pay:</label>
                <span style="color:#555;font-size:1rem">$</span>
                <input type="number"
                       class="amount-input pledge-amount"
                       data-pledge="{{ $pledge->id }}"
                       value="{{ number_format((float)$pledge->balance, 2, '.', '') }}"
                       min="0.01"
                       max="{{ number_format((float)$pledge->balance, 2, '.', '') }}"
                       step="0.01">
                <button type="button" class="full-btn" onclick="setFull(this)">Pay full amount</button>
            </div>
            <button type="button" class="restore-btn" style="display:none" onclick="restorePledge(this)">+ Restore to payment</button>
        </div>
        @endforeach

        <div class="fee-row">
            <label>
                <input type="checkbox" id="cover-fee-checkbox">
                Cover 2% processing fee?
            </label>
            <span class="fee-note" id="fee-note" style="display:none"></span>
        </div>

        <div class="total-row">
            <span class="total-label">Total Payment</span>
            <span class="total-amount" id="total-display">$0.00</span>
        </div>

        {{-- Payment method tabs (Apple Pay / Google Pay shown dynamically if eligible) --}}
        <div class="method-tabs" id="method-tabs" style="display:none">
            <button class="method-tab active" data-method="paypal">PayPal</button>
            @if(config('app.apple_pay_enabled'))
            <button class="method-tab apple-pay-tab" data-method="applepay" style="display:none">Apple Pay</button>
            @endif
            <button class="method-tab google-pay-tab" data-method="googlepay" style="display:none">Google Pay</button>
        </div>

        <div class="method-content active" id="method-paypal">
            <div id="paypal-button-container"></div>
        </div>
        @if(config('app.apple_pay_enabled'))
        <div class="method-content" id="method-applepay">
            <button type="button" id="apple-pay-btn" class="apple-pay-button"></button>
        </div>
        @endif
        <div class="method-content" id="method-googlepay">
            <div id="google-pay-container"></div>
        </div>

        <div id="success-box" class="success-box" style="display:none">
            <div class="success-title">Payment Received — Thank You!</div>
            <p class="success-sub">Your payment of <strong id="success-amount"></strong> has been processed.<br>
            Your account will be updated shortly.</p>
        </div>
    </div>
    @endif

    <div class="footer">
        {{ $tenant->name ?? config('app.name') }}@if($tenant->org_address ?? null) &bull; {{ $tenant->org_address }}@endif<br>
        @if($tenant->org_email ?? null)Questions? Contact us at <a href="mailto:{{ $tenant->org_email }}" style="color:#c9a84c">{{ $tenant->org_email }}</a>@if($tenant->org_phone ?? null) or {{ $tenant->org_phone }}@endif@endif
    </div>
</div>

{{-- Google Pay SDK --}}
<script src="https://pay.google.com/gp/p/js/pay.js"></script>

{{-- PayPal JS SDK: buttons + Google Pay --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&components=buttons,googlepay&enable-funding=card,venmo" data-page-type="checkout"></script>

@if(config('app.apple_pay_enabled'))
{{-- PayPal Apple Pay — separate namespace to avoid conflict --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&components=applepay" data-namespace="paypalApplepay"></script>
@endif

<script>
var TOKEN = '{{ $token }}';
var CSRF  = '{{ csrf_token() }}';

// ── Pledge helpers ─────────────────────────────────────────────────────────

function getAmounts() {
    var amounts = {};
    document.querySelectorAll('.pledge-amount').forEach(function(inp) {
        var val = parseFloat(inp.value) || 0;
        if (val > 0) amounts[inp.dataset.pledge] = val;
    });
    return amounts;
}

function getBaseTotal() {
    return Object.values(getAmounts()).reduce(function(s, v) { return s + v; }, 0);
}

function coveringFee() {
    return document.getElementById('cover-fee-checkbox').checked;
}

function getFeeAmount() {
    return coveringFee() ? Math.round(getBaseTotal() * 0.02 * 100) / 100 : 0;
}

function getTotal() {
    return Math.round((getBaseTotal() + getFeeAmount()) * 100) / 100;
}

function updateTotal() {
    var base = getBaseTotal();
    var fee  = getFeeAmount();
    document.getElementById('total-display').textContent = '$' + (base + fee).toFixed(2);
    var feeNote = document.getElementById('fee-note');
    if (fee > 0) {
        feeNote.textContent = '+$' + fee.toFixed(2) + ' fee';
        feeNote.style.display = '';
    } else {
        feeNote.style.display = 'none';
    }
}

document.getElementById('cover-fee-checkbox').addEventListener('change', updateTotal);

function setFull(btn) {
    var row = btn.closest('.pledge-row');
    row.querySelector('.pledge-amount').value = row.dataset.balance;
    updateTotal();
}

function removePledge(btn) {
    var row = btn.closest('.pledge-row');
    row.querySelector('.pledge-amount').value = 0;
    row.classList.add('removed');
    row.querySelector('.restore-btn').style.display = 'block';
    updateTotal();
}

function restorePledge(btn) {
    var row = btn.closest('.pledge-row');
    row.querySelector('.pledge-amount').value = row.dataset.balance;
    row.classList.remove('removed');
    btn.style.display = 'none';
    updateTotal();
}

document.querySelectorAll('.pledge-amount').forEach(function(inp) {
    inp.addEventListener('input', updateTotal);
});
updateTotal();

// ── Error / success ────────────────────────────────────────────────────────

function showError(msg) {
    var box = document.getElementById('error-box');
    box.textContent = msg;
    box.style.display = 'block';
}

function hideError() {
    document.getElementById('error-box').style.display = 'none';
}

function showSuccess(amount) {
    document.querySelectorAll('.pledge-row').forEach(function(r) { r.style.display = 'none'; });
    document.querySelector('.total-row').style.display = 'none';
    document.getElementById('method-tabs').style.display = 'none';
    document.querySelectorAll('.method-content').forEach(function(c) { c.style.display = 'none'; });
    document.getElementById('success-amount').textContent = '$' + parseFloat(amount).toFixed(2);
    document.getElementById('success-box').style.display = 'block';
}

// ── Payment API calls ──────────────────────────────────────────────────────

function createOrder() {
    var amounts = getAmounts();
    if (!Object.keys(amounts).length) {
        showError('Please select at least one pledge to pay.');
        return Promise.reject(new Error('no amounts'));
    }
    hideError();
    return fetch('/pay/' + TOKEN + '/create-order', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ amounts: amounts, cover_fee: coveringFee() }),
    }).then(function(r) {
        return r.json().then(function(data) {
            if (!r.ok) throw new Error(data.error || 'Failed to create order.');
            return data.id;
        });
    });
}

function captureOrder(orderID) {
    return fetch('/pay/' + TOKEN + '/capture', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ orderID: orderID }),
    }).then(function(r) {
        return r.json().then(function(data) {
            if (!r.ok) throw new Error(data.error || 'Payment capture failed.');
            return data;
        });
    });
}

// ── Method tabs ────────────────────────────────────────────────────────────

function revealMethodTabs() {
    document.getElementById('method-tabs').style.display = 'flex';
}

document.querySelectorAll('.method-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.method-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.method-content').forEach(function(c) { c.classList.remove('active'); });
        tab.classList.add('active');
        var content = document.getElementById('method-' + tab.dataset.method);
        if (content) content.classList.add('active');
        hideError();
    });
});

// ── PayPal Buttons ─────────────────────────────────────────────────────────

paypal.Buttons({
    style: { color: 'gold', shape: 'rect', label: 'pay', height: 48 },
    createOrder: function() { return createOrder(); },
    onApprove: function(data) {
        return captureOrder(data.orderID).then(function(result) {
            if (result.success) {
                showSuccess(result.amount);
            } else {
                showError(result.error || 'Payment failed. Please contact the office.');
            }
        });
    },
    onError: function(err) {
        showError('A payment error occurred. Please try again or contact the office.');
        console.error(err);
    },
}).render('#paypal-button-container');

@if(config('app.apple_pay_enabled'))
// ── Apple Pay ──────────────────────────────────────────────────────────────

(function () {
    if (typeof window.paypalApplepay === 'undefined' || typeof window.paypalApplepay.Applepay !== 'function') return;

    window.paypalApplepay.Applepay().config().then(function (config) {
        if (!config.isEligible) return;

        document.querySelector('.apple-pay-tab').style.display = '';
        revealMethodTabs();

        document.getElementById('apple-pay-btn').addEventListener('click', function () {
            hideError();
            var total = getTotal();
            if (total <= 0) { showError('Please select at least one pledge to pay.'); return; }

            var applepay = window.paypalApplepay.Applepay();

            var session = new ApplePaySession(4, {
                countryCode:          'US',
                currencyCode:         'USD',
                merchantCapabilities: config.merchantCapabilities,
                supportedNetworks:    config.supportedNetworks,
                total: { label: '{{ $tenant->name ?? config("app.name") }}', type: 'final', amount: total.toFixed(2) },
            });

            session.onvalidatemerchant = function (event) {
                applepay.validateMerchant({
                    validationUrl: event.validationURL,
                    displayName:   '{{ $tenant->name ?? config("app.name") }}',
                }).then(function (result) {
                    session.completeMerchantValidation(result.merchantSession);
                }).catch(function (err) {
                    showError('Apple Pay setup failed: ' + ((err && err.message) || 'Unknown error'));
                    session.abort();
                });
            };

            session.onpaymentauthorized = function (event) {
                createOrder().then(function (orderId) {
                    var token = event.payment.token;
                    if (typeof token === 'string') {
                        try { token = JSON.parse(token); } catch(e) {}
                    }
                    var confirmPayload = { orderId: orderId, token: token };
                    if (event.payment.billingContact) confirmPayload.billingContact = event.payment.billingContact;
                    return applepay.confirmOrder(confirmPayload).then(function () {
                        return captureOrder(orderId);
                    });
                }).then(function (result) {
                    session.completePayment(ApplePaySession.STATUS_SUCCESS);
                    showSuccess(result.amount);
                }).catch(function (err) {
                    session.completePayment(ApplePaySession.STATUS_FAILURE);
                    showError('Apple Pay failed: ' + ((err && err.message) || 'Unknown error'));
                });
            };

            session.begin();
        });
    }).catch(function () {});
})();
@endif

// ── Google Pay ─────────────────────────────────────────────────────────────

(function () {
    if (typeof paypal.Googlepay !== 'function') return;
    if (typeof google === 'undefined' || !google.payments) return;

    var googlepay = paypal.Googlepay();

    googlepay.config().then(function (config) {
        var gpClient = new google.payments.api.PaymentsClient({ environment: 'PRODUCTION' });

        return gpClient.isReadyToPay({ apiVersion: 2, apiVersionMinor: 0, allowedPaymentMethods: config.allowedPaymentMethods })
                       .then(function (ready) {
            if (!ready.result) return;

            document.querySelector('.google-pay-tab').style.display = '';
            revealMethodTabs();

            var gpButton = gpClient.createButton({
                buttonColor:    'black',
                buttonType:     'pay',
                buttonSizeMode: 'fill',
                onClick: function () {
                    hideError();
                    var total = getTotal();
                    if (total <= 0) { showError('Please select at least one pledge to pay.'); return; }

                    createOrder().then(function (orderId) {
                        return gpClient.loadPaymentData({
                            apiVersion:            2,
                            apiVersionMinor:       0,
                            allowedPaymentMethods: config.allowedPaymentMethods,
                            merchantInfo:          config.merchantInfo,
                            transactionInfo: {
                                countryCode:      'US',
                                currencyCode:     'USD',
                                totalPriceStatus: 'FINAL',
                                totalPrice:       total.toFixed(2),
                                totalPriceLabel:  'Payment to {{ $tenant->name ?? config("app.name") }}',
                            },
                        }).then(function (paymentData) {
                            return googlepay.confirmOrder({
                                orderId:           orderId,
                                paymentMethodData: paymentData.paymentMethodData,
                            });
                        }).then(function () {
                            return captureOrder(orderId);
                        }).then(function (result) {
                            showSuccess(result.amount);
                        });
                    }).catch(function (err) {
                        if (!err || err.statusCode !== 'CANCELED') {
                            showError((err && err.message) || 'Google Pay payment failed. Please try again.');
                        }
                    });
                },
            });

            document.getElementById('google-pay-container').appendChild(gpButton);
        });
    }).catch(function () {});
})();
</script>
</body>
</html>
