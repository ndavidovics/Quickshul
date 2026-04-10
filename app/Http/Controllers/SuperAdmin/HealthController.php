<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs  = DB::table('failed_jobs')->count();

        $diskTotal = disk_total_space('/');
        $diskFree  = disk_free_space('/');

        $logFile = storage_path('logs/laravel.log');
        $logSize = file_exists($logFile) ? filesize($logFile) : 0;

        $dbName = config('database.connections.mysql.database');
        $rawRows = DB::select("
            SELECT table_name, table_rows,
                   ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = ?
              AND table_name IN (
                'families','users','payments','pledges',
                'failed_jobs','jobs','sessions','qb_sync_logs',
                'family_members','yahrtzeits'
              )
            ORDER BY (data_length + index_length) DESC
        ", [$dbName]);

        // MySQL information_schema may return column names in uppercase depending on server config.
        // Normalize to lowercase so the view can reliably use $t->table_name etc.
        $tableSizes = collect($rawRows)->map(function ($row) {
            $a = (array) $row;
            $lower = array_combine(array_map('strtolower', array_keys($a)), array_values($a));
            return (object) $lower;
        })->all();

        $appInfo = [
            'Laravel Version' => app()->version(),
            'PHP Version'     => PHP_VERSION,
            'Environment'     => config('app.env'),
            'Debug Mode'      => config('app.debug') ? 'ON ⚠️' : 'off',
            'Cache Driver'    => config('cache.default'),
            'Queue Driver'    => config('queue.default'),
            'Session Driver'  => config('session.driver'),
        ];

        return view('superadmin.health.index', compact(
            'pendingJobs', 'failedJobs',
            'diskTotal', 'diskFree',
            'logSize', 'tableSizes', 'appInfo'
        ));
    }
}
