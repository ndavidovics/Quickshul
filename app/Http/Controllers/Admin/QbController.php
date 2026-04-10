<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DailyQuickBooksSync;
use App\Models\Family;
use App\Models\QbConflict;
use App\Models\QbConnection;
use App\Models\QbSyncLog;
use App\Services\AuditService;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;

class QbController extends Controller
{
    public function __construct(
        private QuickBooksService $qbService,
        private AuditService $audit
    ) {}

    public function index()
    {
        $connection      = $this->qbService->getConnection();
        $isConnected     = $this->qbService->isConnected();
        $lastSync        = QbSyncLog::latest()->first();
        $unresolvedCount = QbConflict::unresolved()->count();
        $recentLogs      = QbSyncLog::latest()->limit(10)->get();

        // Warn if membership types have no QB labels mapped yet
        $unmappedTypes = \App\Models\MembershipType::where('active', true)
            ->get()
            ->filter(fn($t) => empty($t->qb_labels));

        return view('admin.qb.index', compact('connection', 'isConnected', 'lastSync', 'unresolvedCount', 'recentLogs', 'unmappedTypes'));
    }

    public function connect()
    {
        return view('admin.qb.connect', ['isConnected' => $this->qbService->isConnected()]);
    }

    public function redirect()
    {
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;

        // Store tenant_id server-side in session so the root-domain callback can't be forged
        session(['qb_oauth_tenant_id' => $tenantId]);

        $url = $this->qbService->getAuthorizationUrl($tenantId);
        return redirect($url);
    }

    // Called from admin subdomain — tenant is already bound
    public function callback(Request $request)
    {
        $code    = $request->query('code');
        $realmId = $request->query('realmId');

        if (!$code || !$realmId) {
            return redirect()->route('admin.qb.connect')->withErrors(['error' => 'Authorization failed — missing code or realm ID.']);
        }

        try {
            $conn = $this->qbService->exchangeCodeForTokens($code, $realmId);
            $this->audit->log('admin.qb.connected', null, [], ['realm_id' => $realmId], 'QuickBooks connected successfully');
            return redirect()->route('admin.membership-types.index')->with('qb_just_connected', true);
        } catch (\Throwable $e) {
            \Log::error('QB callback error: ' . $e->getMessage());
            return redirect()->route('admin.qb.connect')->withErrors(['error' => 'Failed to connect: ' . $e->getMessage()]);
        }
    }

    // Called from root domain (quickshul.com/auth/qb/callback) — no tenant bound
    public function rootCallback(Request $request)
    {
        $code    = $request->query('code');
        $realmId = $request->query('realmId');
        $state   = $request->query('state', '');

        if (!$code || !$realmId) {
            return redirect('/')->withErrors(['error' => 'QuickBooks authorization failed.']);
        }

        // Prefer server-side session (set during redirect) over state param to prevent forgery
        $tenantId = session('qb_oauth_tenant_id');
        if (!$tenantId) {
            // Fallback: decode from state param (older flow)
            $decoded  = json_decode(base64_decode($state), true);
            $tenantId = $decoded['tenant_id'] ?? null;
        }

        if (!$tenantId) {
            \Log::error('QB rootCallback: no tenant_id in session or state');
            return redirect('/')->withErrors(['error' => 'Could not identify your organization. Please try connecting again.']);
        }

        session()->forget('qb_oauth_tenant_id');

        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant) {
            return redirect('/')->withErrors(['error' => 'Organization not found.']);
        }

        // Bind tenant so HasTenant scopes work correctly
        app()->instance('tenant', $tenant);

        try {
            $this->qbService->exchangeCodeForTokens($code, $realmId);
            $this->audit->log('admin.qb.connected', null, [], ['realm_id' => $realmId], 'QuickBooks connected successfully');

            $subdomain = 'https://' . $tenant->slug . '.' . config('app.root_domain');
            return redirect($subdomain . '/admin/settings/membership')->with('qb_just_connected', true);
        } catch (\Throwable $e) {
            \Log::error('QB rootCallback error: ' . $e->getMessage());
            $subdomain = 'https://' . $tenant->slug . '.' . config('app.root_domain');
            return redirect($subdomain . '/admin/qb/connect')->withErrors(['error' => 'Failed to connect: ' . $e->getMessage()]);
        }
    }

    public function disconnect()
    {
        QbConnection::query()->delete();
        $this->audit->log('admin.qb.disconnected', null, [], [], 'QuickBooks disconnected');
        return redirect()->route('admin.qb')->with('success', 'QuickBooks disconnected.');
    }

    public function syncPull(Request $request)
    {
        $forced   = $request->boolean('forced', false);
        $label    = $forced ? 'Full sync' : 'Update sync';
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;

        DailyQuickBooksSync::dispatch($forced, auth()->id(), $tenantId);

        $this->audit->log('admin.qb.sync.pull.triggered', null, [], ['forced' => $forced], "Manual QB pull sync queued ({$label})");
        return back()->with('success', "{$label} queued. This may take several minutes — refresh the page to see results.");
    }

    public function syncPushFamily(int $id)
    {
        if (!$this->qbService->isConnected()) {
            return back()->withErrors(['error' => 'QuickBooks is not connected.']);
        }

        $family = Family::findOrFail($id);

        try {
            $this->qbService->updateCustomer($family);
            $family->update(['qb_last_sync_at' => now()]);
            $this->audit->log('admin.qb.sync.push', $family, [], [], "Pushed {$family->name} to QuickBooks");
            return back()->with('success', "{$family->name} pushed to QuickBooks.");
        } catch (\Throwable $e) {
            \Log::error("QB push failed for family {$id}: " . $e->getMessage());
            return back()->withErrors(['error' => 'QB push failed: ' . $e->getMessage()]);
        }
    }

    public function conflicts(Request $request)
    {
        $conflicts = QbConflict::unresolved()
            ->latest()
            ->paginate(30);

        return view('admin.qb.conflicts', compact('conflicts'));
    }

    public function resolveConflict(Request $request, int $id)
    {
        $conflict = QbConflict::findOrFail($id);

        $request->validate([
            'resolution' => 'required|in:portal,qb',
        ]);

        $resolution = $request->resolution;

        // Apply the winning value
        if ($resolution === 'qb') {
            $model = $conflict->entity_model;
            if ($model) {
                $model->update([$conflict->field => $conflict->qb_value]);
            }
        }
        // 'portal' = keep portal value, no model update needed

        $conflict->update([
            'resolved'             => true,
            'resolved_by_user_id'  => auth()->id(),
            'resolved_at'          => now(),
            'resolution'           => $resolution,
        ]);

        $this->audit->log('admin.qb.conflict.resolved', null, [], ['conflict_id' => $id, 'resolution' => $resolution], "Conflict #{$id} resolved: chose {$resolution} value for field {$conflict->field}");

        return back()->with('success', 'Conflict resolved.');
    }
}
