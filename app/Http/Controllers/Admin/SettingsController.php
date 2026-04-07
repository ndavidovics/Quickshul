<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $tenant = app('tenant');
        return view('admin.settings.index', compact('tenant'));
    }

    // -------------------------------------------------------------------------
    // Organization Profile
    // -------------------------------------------------------------------------

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:120',
            'tagline'     => 'nullable|string|max:200',
            'org_address' => 'nullable|string|max:255',
            'org_phone'   => 'nullable|string|max:40',
            'org_email'   => 'nullable|email|max:120',
            'logo_url'    => 'nullable|url|max:500',
            'timezone'    => 'nullable|string|max:60',
        ]);

        $tenant = app('tenant');
        $tenant->update($request->only(['name', 'tagline', 'org_address', 'org_phone', 'org_email', 'logo_url', 'timezone']));

        $this->audit->log('admin.settings.profile.updated', null, [], [], 'Organization profile updated');

        return back()->with('success', 'Organization profile saved.');
    }

    // -------------------------------------------------------------------------
    // Gmail
    // -------------------------------------------------------------------------

    public function connectGmail()
    {
        $tenant = app('tenant');
        $state = base64_encode(json_encode([
            'context'   => 'settings',
            'tenant_id' => $tenant->id,
        ]));

        $params = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => config('services.google.gmail_redirect'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/gmail.send https://www.googleapis.com/auth/userinfo.email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    public function disconnectGmail()
    {
        $tenant = app('tenant');
        $tenant->update([
            'gmail_access_token'     => null,
            'gmail_refresh_token'    => null,
            'gmail_token_expires_at' => null,
            'gmail_email'            => null,
        ]);

        $this->audit->log('admin.settings.gmail.disconnected', null, [], [], 'Gmail disconnected');

        return back()->with('success', 'Gmail disconnected.');
    }

    // -------------------------------------------------------------------------
    // PayPal
    // -------------------------------------------------------------------------

    public function updatePaypal(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'paypal_client_id' => 'required|string|max:200',
            'paypal_secret'    => 'nullable|string|max:200',
            'paypal_mode'      => 'required|in:sandbox,live',
        ]);

        // Use existing secret if not provided
        $secret = $request->filled('paypal_secret')
            ? $request->paypal_secret
            : $tenant->paypal_secret;

        if (!$secret) {
            return back()->withErrors(['paypal_secret' => 'Secret is required when connecting PayPal for the first time.']);
        }

        // Validate credentials against PayPal API before saving
        $baseUrl = $request->paypal_mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $response = Http::withBasicAuth($request->paypal_client_id, $secret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$response->successful()) {
            return back()->withErrors(['paypal_client_id' => 'PayPal credentials are invalid. Please check your Client ID and Secret.']);
        }

        $tenant->update([
            'paypal_client_id' => $request->paypal_client_id,
            'paypal_secret'    => $secret,
            'paypal_mode'      => $request->paypal_mode,
        ]);

        $this->audit->log('admin.settings.paypal.updated', null, [], [], 'PayPal credentials updated');

        return back()->with('success', 'PayPal credentials verified and saved.');
    }

    // -------------------------------------------------------------------------
    // QuickBooks
    // -------------------------------------------------------------------------

    public function toggleQb(Request $request)
    {
        $tenant = app('tenant');
        $enabled = !$tenant->qb_enabled;
        $tenant->update(['qb_enabled' => $enabled]);

        $this->audit->log('admin.settings.qb.toggled', null, [], ['enabled' => $enabled],
            'QuickBooks ' . ($enabled ? 'enabled' : 'disabled'));

        return back()->with('success', 'QuickBooks ' . ($enabled ? 'enabled.' : 'disabled.'));
    }
}
