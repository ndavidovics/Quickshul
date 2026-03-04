@extends('layouts.app')
@section('title', 'Make a Donation')

@section('content')
@php
    $paypalClientId = config('paypal.' . config('paypal.mode') . '.client_id');
@endphp

<div style="max-width:560px;margin:0 auto">
    <h1 class="page-title">Make a Donation</h1>
    <p class="page-subtitle">Support Young Israel of Memphis</p>

    {{-- ── Success State ─────────────────────────────────────────────────────── --}}
    <div id="donation-success" style="display:none">
        <div class="card" style="text-align:center;padding:2.5rem 2rem">
            <div style="font-size:3rem;margin-bottom:1rem">&#x1F64F;</div>
            <h2 style="color:var(--navy);font-family:'Playfair Display',serif;margin:0 0 0.5rem">Thank You!</h2>
            <p style="color:var(--text-muted);margin:0 0 0.25rem">
                Your donation of <strong id="success-amount" style="color:var(--gold)"></strong> has been received.
            </p>
            <p class="text-sm text-muted" id="success-description" style="margin:0 0 1.5rem"></p>
            <a href="{{ route('dashboard') }}" class="btn btn-gold">Return to Dashboard</a>
        </div>
    </div>

    {{-- ── Donation Form ────────────────────────────────────────────────────── --}}
    <div id="donation-form">
        <div class="card">

            {{-- Amount --}}
            <div class="form-group">
                <label class="form-label">Donation Amount</label>
                <div style="position:relative">
                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);
                                 color:var(--text-muted);font-weight:600">$</span>
                    <input type="number" id="amount" class="form-control" style="padding-left:1.75rem"
                           min="1" step="0.01" placeholder="0.00" autofocus required>
                </div>
            </div>

            {{-- Dedication --}}
            <div class="form-group">
                <label class="form-label">
                    Dedication / Purpose <span class="text-muted text-sm">(optional)</span>
                </label>
                <input type="text" id="description" class="form-control"
                       placeholder="e.g. In memory of..., General donation, Building fund...">
            </div>

            {{-- Error message --}}
            <div id="payment-error"
                 style="display:none;background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;
                        padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem">
            </div>

            {{-- Payment Method Tabs --}}
            <div style="margin-top:0.25rem">
                <label class="form-label" style="margin-bottom:0.6rem">Payment Method</label>

                <div id="payment-tabs" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem">
                    <button type="button" class="pay-tab active" data-tab="card">&#x1F4B3; Credit Card</button>
                    <button type="button" class="pay-tab" data-tab="paypal">
                        <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/PP_logo_h_100x26.png"
                             alt="PayPal" height="13" style="vertical-align:middle;margin-right:3px"> PayPal
                    </button>
                    <button type="button" class="pay-tab apple-pay-tab" data-tab="applepay" style="display:none">
                        &#xF8FF; Apple Pay
                    </button>
                    <button type="button" class="pay-tab google-pay-tab" data-tab="googlepay" style="display:none">
                        G Pay
                    </button>
                </div>

                {{-- ── Credit Card Tab ── --}}
                <div id="tab-card" class="pay-tab-content">
                    <div class="form-group">
                        <label class="form-label">Name on Card</label>
                        <input type="text" id="card-name" class="form-control"
                               placeholder="Full name as it appears on card" autocomplete="cc-name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Number</label>
                        <div id="card-number-field" class="hosted-field-wrap"></div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Expiry</label>
                            <div id="expiry-date-field" class="hosted-field-wrap"></div>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">CVV</label>
                            <div id="cvv-field" class="hosted-field-wrap"></div>
                        </div>
                    </div>

                    <div style="margin-top:1.25rem">
                        <button type="button" id="card-donate-btn" class="btn btn-gold" style="width:100%">
                            Donate <span id="card-amount-label"></span>
                        </button>
                    </div>

                    <div id="card-not-eligible"
                         style="display:none;text-align:center;padding:1rem;color:var(--text-muted);font-size:0.875rem">
                        Direct card payments require Advanced Credit and Debit Card Payments to be
                        enabled on your PayPal Business account. Please use the PayPal tab instead.
                    </div>
                </div>

                {{-- ── PayPal Tab ── --}}
                <div id="tab-paypal" class="pay-tab-content" style="display:none">
                    <p class="text-sm text-muted" style="margin-bottom:1rem">
                        A secure PayPal window will open — you can pay with your PayPal balance,
                        bank account, or credit card without leaving this page.
                    </p>
                    <div id="paypal-button-container"></div>
                </div>

                {{-- ── Apple Pay Tab ── --}}
                <div id="tab-applepay" class="pay-tab-content" style="display:none">
                    <p class="text-sm text-muted" style="margin-bottom:1rem">
                        Donate using Touch ID or Face ID with Apple Pay.
                    </p>
                    <button type="button" id="apple-pay-btn" class="apple-pay-button">
                        Donate with&nbsp;&nbsp;&#xF8FF;&nbsp;Pay
                    </button>
                </div>

                {{-- ── Google Pay Tab ── --}}
                <div id="tab-googlepay" class="pay-tab-content" style="display:none">
                    <p class="text-sm text-muted" style="margin-bottom:1rem">
                        Donate quickly using Google Pay.
                    </p>
                    <div id="google-pay-container"></div>
                </div>
            </div>

            <div style="margin-top:1.25rem;display:flex;justify-content:flex-end">
                <a href="{{ route('dashboard') }}" class="btn btn-outline">Cancel</a>
            </div>

        </div>
    </div>
</div>

<style>
.pay-tab {
    padding: 0.45rem 0.9rem;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    background: #fff;
    cursor: pointer;
    font-size: 0.825rem;
    font-weight: 500;
    color: var(--text-muted, #6b7280);
    transition: border-color 0.15s, background 0.15s, color 0.15s;
    line-height: 1.4;
}
.pay-tab:hover {
    border-color: var(--gold, #c9a84c);
    color: var(--navy, #1a2d5a);
}
.pay-tab.active {
    border-color: var(--gold, #c9a84c);
    background: var(--gold, #c9a84c);
    color: #fff;
}
/* PayPal Hosted Fields containers */
.hosted-field-wrap {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0 0.75rem;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    overflow: hidden;
}
.hosted-field-wrap.hf-focused {
    border-color: var(--gold, #c9a84c);
    box-shadow: 0 0 0 3px rgba(201,168,76,0.18);
}
.hosted-field-wrap.hf-invalid {
    border-color: #dc2626;
}
.apple-pay-button {
    display: block;
    width: 100%;
    height: 48px;
    border-radius: 8px;
    background: #000;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 1.05rem;
    font-family: -apple-system, 'SF Pro Display', sans-serif;
    letter-spacing: 0.02em;
    transition: opacity 0.15s;
}
.apple-pay-button:hover { opacity: 0.85; }
#google-pay-container > button { width: 100% !important; border-radius: 8px !important; }
</style>

{{-- Google Pay SDK --}}
<script src="https://pay.google.com/gp/p/js/pay.js"></script>

{{-- PayPal JS SDK --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&intent=capture&components=buttons,hosted-fields,applepay,googlepay"></script>

<script>
(function () {
    'use strict';

    var csrf = '{{ csrf_token() }}';

    // ── Helpers ────────────────────────────────────────────────────────────────

    function getAmount()      { return parseFloat(document.getElementById('amount').value) || 0; }
    function getDescription() { return document.getElementById('description').value.trim() || 'General Donation'; }

    function updateAmountLabel() {
        var amt = getAmount();
        var el  = document.getElementById('card-amount-label');
        if (el) el.textContent = amt >= 1 ? '$' + amt.toFixed(2) : '';
    }
    document.getElementById('amount').addEventListener('input', updateAmountLabel);
    updateAmountLabel();

    function showError(msg) {
        var el = document.getElementById('payment-error');
        el.textContent = msg;
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideError() { document.getElementById('payment-error').style.display = 'none'; }

    function showSuccess(amount, description) {
        document.getElementById('donation-form').style.display  = 'none';
        document.getElementById('donation-success').style.display = 'block';
        document.getElementById('success-amount').textContent   = '$' + amount;
        var descEl = document.getElementById('success-description');
        descEl.textContent = (description && description !== 'General Donation') ? description : '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateAmount() {
        var amt = getAmount();
        if (amt < 1)     { showError('Please enter a donation amount of at least $1.00.'); document.getElementById('amount').focus(); return false; }
        if (amt > 99999) { showError('Donation amount cannot exceed $99,999.'); return false; }
        return true;
    }

    // ── Tab switching ──────────────────────────────────────────────────────────

    document.querySelectorAll('.pay-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.pay-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.pay-tab-content').forEach(function (c) { c.style.display = 'none'; });
            this.classList.add('active');
            document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
            hideError();
        });
    });

    // ── Server API calls ───────────────────────────────────────────────────────

    function createOrder() {
        return fetch('{{ route('donate.create-order') }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ amount: getAmount(), description: getDescription() }),
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.error || 'Failed to create order.');
                return data.id;
            });
        });
    }

    function captureOrder(orderID) {
        return fetch('{{ route('donate.capture') }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify({ orderID: orderID }),
        }).then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.error || 'Payment capture failed.');
                return data;
            });
        });
    }

    // ── PayPal Buttons ─────────────────────────────────────────────────────────

    paypal.Buttons({
        style: { layout: 'vertical', color: 'gold', shape: 'rect', label: 'donate' },
        createOrder: function () {
            hideError();
            if (!validateAmount()) return Promise.reject(new Error('Invalid amount'));
            return createOrder();
        },
        onApprove: function (data) {
            return captureOrder(data.orderID).then(function (result) {
                showSuccess(result.amount, result.description);
            }).catch(function (err) {
                showError(err.message || 'Donation failed. Please contact the office.');
            });
        },
        onError:  function () { showError('PayPal encountered an error. Please try again.'); },
        onCancel: function () { showError('PayPal payment was cancelled.'); },
    }).render('#paypal-button-container');

    // ── Hosted Fields (Credit Card) ────────────────────────────────────────────

    var fieldMap = { number: 'card-number-field', expirationDate: 'expiry-date-field', cvv: 'cvv-field' };

    // Attempt render unconditionally — isEligible() is unreliable when other
    // components (applepay/googlepay) are loaded alongside hosted-fields.
    paypal.HostedFields.render({
            createOrder: function () {
                hideError();
                if (!validateAmount()) return Promise.reject(new Error('Invalid amount'));
                return createOrder();
            },
            styles: {
                'input':    { 'font-family': 'Inter, system-ui, sans-serif', 'font-size': '14px', 'color': '#1a2d5a' },
                ':focus':   { 'outline': 'none' },
                '.invalid': { 'color': '#dc2626' },
            },
            fields: {
                number:         { selector: '#card-number-field', placeholder: '1234 5678 9012 3456' },
                expirationDate: { selector: '#expiry-date-field', placeholder: 'MM / YY' },
                cvv:            { selector: '#cvv-field',         placeholder: '\u00B7\u00B7\u00B7' },
            },
        }).then(function (hf) {

            hf.on('focus', function (ev) {
                var el = document.getElementById(fieldMap[ev.emittedBy]);
                if (el) el.classList.add('hf-focused');
            });
            hf.on('blur', function (ev) {
                var el = document.getElementById(fieldMap[ev.emittedBy]);
                if (el) el.classList.remove('hf-focused');
            });
            hf.on('validityChange', function (ev) {
                var el  = document.getElementById(fieldMap[ev.emittedBy]);
                var fld = ev.fields[ev.emittedBy];
                if (el) el.classList.toggle('hf-invalid', !fld.isValid && fld.isPotentiallyValid === false);
            });

            document.getElementById('card-donate-btn').addEventListener('click', function () {
                hideError();
                if (!validateAmount()) return;

                var cardName = document.getElementById('card-name').value.trim();
                if (!cardName) { showError('Please enter the name on your card.'); return; }

                var btn = document.getElementById('card-donate-btn');
                btn.disabled    = true;
                btn.textContent = 'Processing\u2026';

                hf.submit({ cardholderName: cardName, contingencies: ['3D_SECURE'] })
                  .then(function (payload) { return captureOrder(payload.orderId); })
                  .then(function (result) {
                      showSuccess(result.amount, result.description);
                  })
                  .catch(function (err) {
                      showError(err.message || 'Card payment failed. Please check your details and try again.');
                      btn.disabled = false;
                      btn.innerHTML = 'Donate <span id="card-amount-label"></span>';
                      updateAmountLabel();
                  });
            });

        }).catch(function () {
            // Render failed — account not enabled for Advanced Card Payments
            document.getElementById('card-donate-btn').style.display = 'none';
            document.getElementById('card-not-eligible').style.display = 'block';
            document.querySelector('[data-tab="paypal"]').click();
        });

    // ── Apple Pay ──────────────────────────────────────────────────────────────

    (function () {
        if (typeof paypal.Applepay !== 'function') return;

        paypal.Applepay().config().then(function (config) {
            if (!config.isEligible) return;

            document.querySelector('.apple-pay-tab').style.display = 'inline-flex';

            document.getElementById('apple-pay-btn').addEventListener('click', function () {
                hideError();
                if (!validateAmount()) return;

                var amount = getAmount();

                createOrder().then(function (orderId) {
                    var applepay = paypal.Applepay();
                    var session  = new ApplePaySession(4, {
                        countryCode:          'US',
                        currencyCode:         'USD',
                        merchantCapabilities: config.merchantCapabilities,
                        supportedNetworks:    config.supportedNetworks,
                        total: { label: 'Young Israel of Memphis', type: 'final', amount: amount.toFixed(2) },
                    });

                    session.onvalidatemerchant = function (event) {
                        applepay.validateMerchant({ validationUrl: event.validationURL, orderId: orderId })
                                .catch(function () { showError('Apple Pay merchant validation failed.'); session.abort(); });
                    };

                    session.onpaymentauthorized = function (event) {
                        applepay.confirmOrder({ orderId: orderId, token: event.payment.token, billingContact: event.payment.billingContact })
                                .then(function () { return captureOrder(orderId); })
                                .then(function (result) {
                                    session.completePayment(ApplePaySession.STATUS_SUCCESS);
                                    showSuccess(result.amount, result.description);
                                })
                                .catch(function () {
                                    session.completePayment(ApplePaySession.STATUS_FAILURE);
                                    showError('Apple Pay payment failed. Please try again.');
                                });
                    };

                    session.begin();
                }).catch(function (err) { showError(err.message); });
            });
        }).catch(function () { /* Apple Pay not available */ });
    })();

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

                document.querySelector('.google-pay-tab').style.display = 'inline-flex';

                var gpButton = gpClient.createButton({
                    buttonColor:    'black',
                    buttonType:     'donate',
                    buttonSizeMode: 'fill',
                    onClick: function () {
                        hideError();
                        if (!validateAmount()) return;

                        var amount = getAmount();

                        createOrder().then(function (orderId) {
                            // Build the loadPaymentData request explicitly — only
                            // pass the fields Google Pay expects; spreading the
                            // full PayPal config object causes OR_BIBED_06.
                            return gpClient.loadPaymentData({
                                apiVersion:            2,
                                apiVersionMinor:       0,
                                allowedPaymentMethods: config.allowedPaymentMethods,
                                merchantInfo:          config.merchantInfo,
                                transactionInfo: {
                                    countryCode:      'US',
                                    currencyCode:     'USD',
                                    totalPriceStatus: 'FINAL',
                                    totalPrice:       amount.toFixed(2),
                                    totalPriceLabel:  'Donation to Young Israel of Memphis',
                                },
                            }).then(function (paymentData) {
                                return googlepay.confirmOrder({
                                    orderId:           orderId,
                                    paymentMethodData: paymentData.paymentMethodData,
                                });
                            }).then(function () {
                                return captureOrder(orderId);
                            }).then(function (result) {
                                showSuccess(result.amount, result.description);
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
        }).catch(function () { /* Google Pay not available */ });
    })();

})();
</script>
@endsection
