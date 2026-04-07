<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display the activity log page.
     */
    public function index(): View
    {
        return view('admin.activity-logs', [
            'logs' => ActivityLog::query()
                ->with('actor')
                ->latest()
                ->paginate(20),
        ]);
    }
}
