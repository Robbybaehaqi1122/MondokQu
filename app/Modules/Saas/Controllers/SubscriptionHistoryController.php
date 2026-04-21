<?php

namespace App\Modules\Saas\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TenantSubscriptionHistory;
use Illuminate\View\View;

class SubscriptionHistoryController extends Controller
{
    /**
     * Display the subscription history list for SaaS operators.
     */
    public function index(): View
    {
        return view('modules.saas.subscription-histories.index', [
            'histories' => TenantSubscriptionHistory::query()
                ->with(['tenant', 'changedByUser'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
