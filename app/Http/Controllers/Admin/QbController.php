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

        return view('admin.qb.index', compact('connection', 'isConnected', 'lastSync', 'unresolvedCount', 'recentLogs'));
    }

    public function connect()
    {
        return view('admin.qb.connect', ['isConnected' => $this->qbService->isConnected()]);
    }

    public function redirect()
    {
        $url = $this->qbService->getAuthorizationUrl();
        return redirect($url);
    }

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
            return redirect()->route('admin.qb')->with('success', 'QuickBooks connected successfully!');
        } catch (\Throwable $e) {
            \Log::error('QB callback error: ' . $e->getMessage());
            return redirect()->route('admin.qb.connect')->withErrors(['error' => 'Failed to connect: ' . $e->getMessage()]);
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
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $forced = $request->boolean('forced', false);
        $label  = $forced ? 'Full sync' : 'Update sync';

        $job = new DailyQuickBooksSync($forced, auth()->id());
        app()->call([$job, 'handle']);
        $this->audit->log('admin.qb.sync.pull.triggered', null, [], ['forced' => $forced], "Manual QB pull sync triggered ({$label})");
        return back()->with('success', "{$label} complete. Check the sync log below for results.");
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
