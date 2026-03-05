@extends('layouts.app')

@php
    $paypalClientId    = config('paypal.' . config('paypal.mode') . '.client_id');
    $prefillPledgeId   = request('pledge_id');
    $prefillAmount     = request('amount');
    $prefillDescription= request('description');
    $isPledgePayment   = !empty($prefillPledgeId);
    $family            = auth()->user()->family;
    $user              = auth()->user();
@endphp

@section('title', $isPledgePayment ? 'Pay Pledge' : 'Make a Donation')

@section('content')

<div style="max-width:520px;margin:0 auto">

    {{-- ── Success State ──────────────────────────────────────────────────────── --}}
    <div id="donation-success" style="display:none">
        <div class="card" style="text-align:center;padding:2.5rem 2rem">
            <div style="font-size:3rem;margin-bottom:1rem">&#x1F64F;</div>
            <h2 style="color:var(--navy);font-family:'Playfair Display',serif;margin:0 0 0.5rem">Thank You!</h2>
            <p style="color:var(--text-muted);margin:0 0 0.25rem">
                Your {{ $isPledgePayment ? 'pledge payment' : 'donation' }} of
                <strong id="success-amount" style="color:var(--gold)"></strong> has been received.
            </p>
            <p class="text-sm text-muted" id="success-description" style="margin:0 0 1.5rem"></p>
            <a href="{{ route('financial') }}" class="btn btn-gold">Return to Financial Dashboard</a>
        </div>
    </div>

    {{-- ── Donation Form ────────────────────────────────────────────────────── --}}
    <div id="donation-form">
        <h1 class="page-title">{{ $isPledgePayment ? 'Pay Pledge' : 'Make a Donation' }}</h1>
        <p class="page-subtitle">Support Young Israel of Memphis</p>

        <div class="card">

            {{-- Error --}}
            <div id="payment-error"
                 style="display:none;background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;
                        padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;font-size:0.875rem"></div>

            {{-- Amount --}}
            <div class="form-group">
                <label class="form-label">{{ $isPledgePayment ? 'Payment Amount' : 'Donation Amount' }}</label>
                <div style="position:relative">
                    <span style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);
                                 color:var(--text-muted);font-weight:600">$</span>
                    <input type="number" id="amount" class="form-control" style="padding-left:1.75rem"
                           min="1" step="0.01" placeholder="0.00" autofocus
                           value="{{ $prefillAmount }}">
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">
                    {{ $isPledgePayment ? 'Description' : 'Dedication / Purpose' }}
                    <span class="text-muted text-sm">(optional)</span>
                </label>
                <input type="text" id="description" class="form-control"
                       placeholder="{{ $isPledgePayment ? 'Pledge description' : 'e.g. In memory of..., Building fund...' }}"
                       value="{{ $prefillDescription }}">
            </div>

            {{-- Hidden pledge ID --}}
            <input type="hidden" id="pledge-id" value="{{ $prefillPledgeId }}">

            <div style="border-top:1px solid var(--border-dim);margin:0.5rem 0 1.25rem"></div>

            {{-- PayPal Buttons (card, PayPal, Venmo, Pay Later all shown natively) --}}
            <div id="paypal-button-container"></div>

            {{-- Google Pay (shown only on eligible devices) --}}
            <div id="google-pay-container" style="display:none;margin-top:0.75rem"></div>

            @if(config('app.apple_pay_enabled'))
            {{-- Apple Pay (shown only on eligible Apple devices) --}}
            <div id="apple-pay-container" style="display:none;margin-top:0.75rem">
                <button type="button" id="apple-pay-btn" class="apple-pay-button" style="
                    display:block;width:100%;padding:0.75rem;border-radius:8px;
                    background:#000;color:#fff;border:none;cursor:pointer;
                    font-size:1.05rem;font-family:-apple-system,'SF Pro Display',sans-serif;
                    letter-spacing:0.02em">
                    Donate with&nbsp;&nbsp;&#xF8FF;&nbsp;Pay
                </button>
            </div>
            @endif

            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-dim);
                        display:flex;justify-content:flex-end">
                <a href="{{ route('financial') }}" class="btn btn-outline">Cancel</a>
            </div>

        </div>
    </div>
</div>

{{-- Google Pay SDK --}}
<script src="https://pay.google.com/gp/p/js/pay.js"></script>

{{-- PayPal JS SDK: buttons + Google Pay. enable-funding=card surfaces card as a native option. --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&intent=capture&components=buttons,googlepay&enable-funding=card,venmo"></script>

@if(config('app.apple_pay_enabled'))
{{-- Apple Pay via separate SDK namespace to avoid conflicts with other components --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&intent=capture&components=applepay" data-namespace="paypalApplepay"></script>
@endif

<script>
(function () {
    'use strict';

    var csrf       = '{{ csrf_token() }}';
    var donorName  = {!! json_encode($user->name  ?? '') !!};
    var donorEmail = {!! json_encode($user->email ?? '') !!};

    // ── Helpers ──────────────────────────────────────────────────────────────

    function getAmount()      { return parseFloat(document.getElementById('amount').value) || 0; }
    function getDescription() { return document.getElementById('description').value.trim() || 'General Donation'; }
    function getPledgeId()    { var el = document.getElementById('pledge-id'); return el ? el.value.trim() : ''; }

    function showError(msg) {
        var el = document.getElementById('payment-error');
        el.textContent = msg;
        el.style.display = 'block';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function hideError() { document.getElementById('payment-error').style.display = 'none'; }

    function showSuccess(amount, description) {
        document.getElementById('donation-form').style.display    = 'none';
        document.getElementById('donation-success').style.display = 'block';
        document.getElementById('success-amount').textContent     = '$' + parseFloat(amount).toFixed(2);
        var descEl = document.getElementById('success-description');
        descEl.textContent = (description && description !== 'General Donation') ? description : '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateAmount() {
        var amt = getAmount();
        if (amt < 1)     { showError('Please enter an amount of at least $1.00.'); document.getElementById('amount').focus(); return false; }
        if (amt > 99999) { showError('Amount cannot exceed $99,999.'); return false; }
        return true;
    }

    // ── API calls ─────────────────────────────────────────────────────────────

    function createOrder() {
        var payload = { amount: getAmount(), description: getDescription() };
        var pledgeId = getPledgeId();
        if (pledgeId) payload.pledge_id = pledgeId;
        if (donorName)  payload.donor_name  = donorName;
        if (donorEmail) payload.donor_email = donorEmail;

        return fetch('{{ route('donate.create-order') }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify(payload),
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

    // ── PayPal Buttons ────────────────────────────────────────────────────────
    // layout:vertical shows all available methods: PayPal, card, Venmo, Pay Later

    paypal.Buttons({
        style: {
            layout:  'vertical',
            color:   'gold',
            shape:   'rect',
            label:   '{{ $isPledgePayment ? "pay" : "donate" }}',
            tagline: false,
        },
        createOrder: function () {
            hideError();
            if (!validateAmount()) return Promise.reject(new Error('Invalid amount'));
            return createOrder();
        },
        onApprove: function (data) {
            return captureOrder(data.orderID).then(function (result) {
                showSuccess(result.amount, result.description);
            }).catch(function (err) {
                showError(err.message || 'Payment processing failed. Please try again.');
            });
        },
        onError:  function (err) { console.error('PayPal error:', err); showError('PayPal encountered an error. Please try again.'); },
        onCancel: function ()    { showError('Payment was cancelled.'); },
    }).render('#paypal-button-container');

    // ── Google Pay ────────────────────────────────────────────────────────────

    (function () {
        if (typeof paypal.Googlepay !== 'function') return;
        if (typeof google === 'undefined' || !google.payments) return;

        var googlepay = paypal.Googlepay();

        googlepay.config().then(function (config) {
            var gpClient = new google.payments.api.PaymentsClient({ environment: 'PRODUCTION' });

            return gpClient.isReadyToPay({
                apiVersion: 2, apiVersionMinor: 0,
                allowedPaymentMethods: config.allowedPaymentMethods,
            }).then(function (ready) {
                if (!ready.result) return;

                document.getElementById('google-pay-container').style.display = 'block';

                var gpButton = gpClient.createButton({
                    buttonColor:    'black',
                    buttonType:     'donate',
                    buttonSizeMode: 'fill',
                    onClick: function () {
                        hideError();
                        if (!validateAmount()) return;

                        var amount = getAmount();

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
                            if (err && err.statusCode === 'CANCELED') return;
                            showError((err && err.message) || 'Google Pay payment failed. Please try again.');
                        });
                    },
                });

                document.getElementById('google-pay-container').appendChild(gpButton);
            });
        }).catch(function () { /* Google Pay not available */ });
    })();

    @if(config('app.apple_pay_enabled'))
    // ── Apple Pay ─────────────────────────────────────────────────────────────
    // Uses separate SDK namespace (paypalApplepay) to avoid conflict with other components.
    // Will show on eligible Apple devices once PayPal activates the Payment Processing
    // Certificate for this merchant account.

    (function () {
        if (typeof window.paypalApplepay === 'undefined' || typeof window.paypalApplepay.Applepay !== 'function') return;

        window.paypalApplepay.Applepay().config().then(function (config) {
            if (!config.isEligible) return;

            document.getElementById('apple-pay-container').style.display = 'block';

            document.getElementById('apple-pay-btn').addEventListener('click', function () {
                hideError();
                if (!validateAmount()) return;

                var amount   = getAmount();
                var applepay = window.paypalApplepay.Applepay();

                var session = new ApplePaySession(4, {
                    countryCode:          'US',
                    currencyCode:         'USD',
                    merchantCapabilities: config.merchantCapabilities,
                    supportedNetworks:    config.supportedNetworks,
                    total: { label: 'Young Israel of Memphis', type: 'final', amount: amount.toFixed(2) },
                });

                session.onvalidatemerchant = function (event) {
                    applepay.validateMerchant({
                        validationUrl: event.validationURL,
                        displayName:   'Young Israel of Memphis',
                    }).then(function (result) {
                        session.completeMerchantValidation(result.merchantSession);
                    }).catch(function (err) {
                        showError('Apple Pay setup failed: ' + ((err && err.message) || 'Unknown error'));
                        session.abort();
                    });
                };

                session.onpaymentauthorized = function (event) {
                    var apPayload = { amount: amount, description: getDescription() };
                    var pledgeId  = getPledgeId();
                    if (pledgeId) apPayload.pledge_id = pledgeId;

                    fetch('{{ route('donate.apple-pay-create-order') }}', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body:    JSON.stringify(apPayload),
                    }).then(function (res) {
                        return res.json().then(function (data) {
                            if (!res.ok) throw new Error(data.error || 'Failed to create order.');
                            return data.id;
                        });
                    }).then(function (orderId) {
                        var token = event.payment.token;
                        if (typeof token === 'string') { try { token = JSON.parse(token); } catch(e) {} }
                        var confirmPayload = { orderId: orderId, token: token };
                        if (event.payment.billingContact) confirmPayload.billingContact = event.payment.billingContact;
                        return applepay.confirmOrder(confirmPayload).then(function () {
                            return captureOrder(orderId);
                        });
                    }).then(function (result) {
                        session.completePayment(ApplePaySession.STATUS_SUCCESS);
                        showSuccess(result.amount, result.description);
                    }).catch(function (err) {
                        session.completePayment(ApplePaySession.STATUS_FAILURE);
                        showError('Apple Pay failed: ' + ((err && err.message) || JSON.stringify(err)));
                    });
                };

                session.begin();
            });
        }).catch(function () { /* Apple Pay not available */ });
    })();
    @endif

})();
</script>
@endsection
