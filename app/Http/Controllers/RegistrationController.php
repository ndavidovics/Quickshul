<?php
namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    // Reserved slugs that cannot be used as subdomain
    const RESERVED_SLUGS = [
        'www', 'app', 'admin', 'api', 'mail', 'superadmin', 'register',
        'login', 'support', 'help', 'status', 'billing', 'portal', 'demo',
        'test', 'staging', 'dev', 'quickshul', 'shul',
    ];

    // Step 1: Show registration form
    public function showRegister(Request $request)
    {
        if ($request->query('forget_google')) {
            session()->forget('register_google');
        }
        return view('register.step1');
    }

    // Redirect to Google for sign-up
    public function redirectToGoogle()
    {
        $state = base64_encode(json_encode(['context' => 'register']));
        $params = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => route('register.google.callback'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'access_type'   => 'offline',
            'state'         => $state,
        ]);
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    // Google OAuth callback for registration
    public function googleCallback(Request $request)
    {
        if ($request->error || !$request->code) {
            return redirect()->route('register')->with('error', 'Google sign-in was cancelled.');
        }

        $response = \Http::post('https://oauth2.googleapis.com/token', [
            'code'          => $request->code,
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri'  => route('register.google.callback'),
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->successful()) {
            return redirect()->route('register')->with('error', 'Could not complete Google sign-in. Please try again.');
        }

        $tokens  = $response->json();
        $profile = \Http::withToken($tokens['access_token'])
            ->get('https://www.googleapis.com/oauth2/v2/userinfo')
            ->json();

        $email    = $profile['email'] ?? null;
        $name     = $profile['name']  ?? null;
        $googleId = $profile['id']    ?? null;

        // If a user with this Google account already exists as a tenant owner, send them to login
        if ($email && User::withoutGlobalScopes()->where('email', $email)->where('is_tenant_owner', true)->exists()) {
            return redirect()->route('login')->with('error', 'An account with that Google address already exists. Please sign in.');
        }

        // Store profile in session so step 1 can pre-fill it
        session(['register_google' => compact('name', 'email', 'googleId')]);

        return redirect()->route('register');
    }

    // Step 1: Submit org details — creates tenant + owner user
    public function register(Request $request)
    {
        $googleData = session('register_google');
        $isGoogleSignup = !empty($googleData);

        $rules = [
            'org_name'   => 'required|string|max:100',
            'slug'       => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9-]+$/',
                            'not_in:' . implode(',', self::RESERVED_SLUGS),
                            'unique:tenants,slug'],
            'admin_name' => 'required|string|max:100',
            'email'      => 'required|email|max:150',
        ];

        if (!$isGoogleSignup) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        // Create tenant
        $tenant = Tenant::create([
            'slug'             => $request->slug,
            'name'             => $request->org_name,
            'status'           => 'pending',
            'onboarding_step'  => 1,
        ]);

        // Seed default membership types
        $tenant->seedDefaultMembershipTypes();

        // Create owner user (no HasTenant scope on creation — set manually)
        $user = new User([
            'name'             => $request->admin_name,
            'email'            => $request->email,
            'password'         => $isGoogleSignup ? null : Hash::make($request->password),
            'google_id'        => $isGoogleSignup ? ($googleData['googleId'] ?? null) : null,
            'is_admin'         => true,
            'is_tenant_owner'  => true,
            'tenant_id'        => $tenant->id,
        ]);
        $user->saveQuietly(); // bypass global scope since tenant not in container yet

        // Clear the Google session data
        session()->forget('register_google');

        // Bind tenant to container for this request
        app()->instance('tenant', $tenant);

        // Log the user in
        Auth::login($user);

        // Redirect to step 2
        return redirect()->route('register.step2');
    }

    // AJAX: check slug availability
    public function checkSlug(Request $request)
    {
        $slug = strtolower(trim($request->slug ?? ''));
        if (in_array($slug, self::RESERVED_SLUGS)) {
            return response()->json(['available' => false, 'reason' => 'reserved']);
        }
        $taken = Tenant::withoutGlobalScopes()->where('slug', $slug)->exists();
        return response()->json(['available' => !$taken]);
    }

    // Step 2: Connect Gmail
    public function showGmail()
    {
        $this->requireOnboarding();
        return view('register.step2', ['tenant' => auth()->user()->tenant ?? $this->currentTenantFromAuth()]);
    }

    // Redirect to Google OAuth for Gmail (gmail.send scope)
    public function connectGmail()
    {
        $this->requireOnboarding();
        $state = base64_encode(json_encode(['step' => 'gmail', 'tenant_id' => $this->currentTenantId()]));
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

    // Gmail OAuth callback
    public function gmailCallback(Request $request)
    {
        if ($request->error || !$request->code) {
            return redirect()->route('register.step2')->with('error', 'Gmail connection cancelled.');
        }

        $state = json_decode(base64_decode($request->state), true);
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($state['tenant_id']);

        // Exchange code for tokens
        $response = \Http::post('https://oauth2.googleapis.com/token', [
            'code'          => $request->code,
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri'  => config('services.google.gmail_redirect'),
            'grant_type'    => 'authorization_code',
        ]);

        if (!$response->successful()) {
            return redirect()->route('register.step2')->with('error', 'Failed to connect Gmail. Please try again.');
        }

        $tokens = $response->json();

        // Get the Gmail address
        $profileResponse = \Http::withToken($tokens['access_token'])
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');
        $email = $profileResponse->json()['email'] ?? null;

        $tenant->update([
            'gmail_access_token'     => $tokens['access_token'],
            'gmail_refresh_token'    => $tokens['refresh_token'] ?? null,
            'gmail_token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            'gmail_email'            => $email,
            'onboarding_step'        => max($tenant->onboarding_step, 2),
        ]);

        // If called from admin settings, redirect back there
        if (($state['context'] ?? '') === 'settings') {
            return redirect("https://{$tenant->slug}." . config('app.root_domain') . '/admin/settings')
                ->with('success', "Gmail connected: {$email}");
        }

        return redirect()->route('register.step3')->with('success', "Gmail connected: {$email}");
    }

    // Skip Gmail
    public function skipGmail()
    {
        $tenant = $this->currentTenantFromAuth();
        if ($tenant->onboarding_step < 2) {
            $tenant->update(['onboarding_step' => 2]);
        }
        return redirect()->route('register.step3');
    }

    // Step 3: Connect PayPal
    public function showPaypal()
    {
        $this->requireOnboarding();
        return view('register.step3', ['tenant' => $this->currentTenantFromAuth()]);
    }

    // Save PayPal credentials + auto-register webhook
    public function connectPaypal(Request $request)
    {
        $this->requireOnboarding();
        $request->validate([
            'paypal_client_id' => 'required|string',
            'paypal_secret'    => 'required|string',
            'paypal_mode'      => 'required|in:live,sandbox',
        ]);

        $tenant = $this->currentTenantFromAuth();

        // Validate PayPal credentials
        $baseUrl = $request->paypal_mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $tokenResponse = \Http::withBasicAuth($request->paypal_client_id, $request->paypal_secret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$tokenResponse->successful()) {
            return back()->withErrors(['paypal_client_id' => 'Invalid PayPal credentials. Please check your Client ID and Secret.']);
        }

        $ppAccessToken = $tokenResponse->json()['access_token'];

        // Auto-register webhook
        $webhookUrl = "https://{$tenant->slug}.quickshul.com/paypal/webhook";
        $webhookResponse = \Http::withToken($ppAccessToken)
            ->post("{$baseUrl}/v1/notifications/webhooks", [
                'url'         => $webhookUrl,
                'event_types' => [
                    ['name' => 'PAYMENT.CAPTURE.COMPLETED'],
                    ['name' => 'PAYMENT.CAPTURE.DENIED'],
                    ['name' => 'CHECKOUT.ORDER.APPROVED'],
                ],
            ]);

        $webhookId = $webhookResponse->json()['id'] ?? null;

        $tenant->update([
            'paypal_client_id'  => $request->paypal_client_id,
            'paypal_secret'     => $request->paypal_secret,
            'paypal_mode'       => $request->paypal_mode,
            'paypal_webhook_id' => $webhookId,
            'onboarding_step'   => max($tenant->onboarding_step, 3),
            'status'            => 'active',
        ]);

        return redirect()->route('register.step4')->with('success', 'PayPal connected' . ($webhookId ? ' and webhook registered.' : '.'));
    }

    // Skip PayPal
    public function skipPaypal()
    {
        $tenant = $this->currentTenantFromAuth();
        $tenant->update([
            'onboarding_step' => max($tenant->onboarding_step, 3),
            'status'          => 'active',
        ]);
        return redirect()->route('register.step4');
    }

    // Step 4: Done
    public function showDone()
    {
        $this->requireOnboarding();
        $tenant = $this->currentTenantFromAuth();
        $tenant->update(['onboarding_step' => 4]);
        return view('register.step4', ['tenant' => $tenant]);
    }

    // -------------------------------------------------------------------------
    private function requireOnboarding()
    {
        if (!auth()->check()) {
            return redirect()->route('register');
        }
    }

    private function currentTenantFromAuth(): Tenant
    {
        return Tenant::withoutGlobalScopes()->findOrFail(auth()->user()->tenant_id);
    }

    private function currentTenantId(): int
    {
        return auth()->user()->tenant_id;
    }
}
