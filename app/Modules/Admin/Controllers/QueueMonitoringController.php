<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $pendingJobs = DB::table('jobs')
            ->select('queue', DB::raw('count(*) as total'), DB::raw('min(created_at) as oldest'), DB::raw('max(created_at) as newest'))
            ->groupBy('queue')
            ->orderBy('queue')
            ->get();

        $pendingTotal = DB::table('jobs')->count();

        $stuckJobs = DB::table('jobs')
            ->where('reserved_at', '<=', now()->subMinutes(30)->timestamp)
            ->where('reserved_at', '!=', 0)
            ->get();

        $failedTotal = DB::table('failed_jobs')->count();

        $failedByQueue = DB::table('failed_jobs')
            ->select('queue', DB::raw('count(*) as total'))
            ->groupBy('queue')
            ->orderBy('queue')
            ->get();

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $recentFailed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.queue-monitoring', [
            'pendingJobs' => $pendingJobs,
            'pendingTotal' => $pendingTotal,
            'stuckJobs' => $stuckJobs,
            'failedTotal' => $failedTotal,
            'failedByQueue' => $failedByQueue,
            'failedJobs' => $failedJobs,
            'recentFailed' => $recentFailed,
        ]);
    }
}
