<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class JobsController extends Controller
{
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(30);

        $failedJobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload, true) ?? [];
            $job->job_name    = class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown Job');
            $job->tenant_name = $this->resolveTenantName($payload);
            $job->short_exception = mb_strimwidth(
                preg_replace('/\s+/', ' ', $job->exception),
                0, 220, '…'
            );
            return $job;
        });

        $pendingCount = DB::table('jobs')->count();
        $failedCount  = DB::table('failed_jobs')->count();

        return view('superadmin.jobs.index', compact('failedJobs', 'pendingCount', 'failedCount'));
    }

    public function retry(int $id)
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if (!$job) return back()->withErrors(['error' => 'Job not found.']);

        Artisan::call('queue:retry', ['id' => [$job->uuid]]);

        return back()->with('success', 'Job re-queued.');
    }

    public function destroy(int $id)
    {
        $job = DB::table('failed_jobs')->where('id', $id)->first();
        if (!$job) return back()->withErrors(['error' => 'Job not found.']);

        Artisan::call('queue:forget', ['id' => $job->uuid]);

        return back()->with('success', 'Failed job deleted.');
    }

    public function destroyAll()
    {
        Artisan::call('queue:flush');
        return back()->with('success', 'All failed jobs cleared.');
    }

    private function resolveTenantName(array $payload): string
    {
        $command = $payload['data']['command'] ?? '';
        if (preg_match('/tenant_id.{1,6}?:(\d+)/', $command, $m)) {
            $tenant = \App\Models\Tenant::withoutGlobalScopes()->find((int) $m[1]);
            return $tenant?->name ?? 'Tenant #' . $m[1];
        }
        return '—';
    }
}
