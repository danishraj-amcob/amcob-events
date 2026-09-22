<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistrationLog;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total' => EventRegistrationLog::count(),
            'confirmed' => EventRegistrationLog::where('status', 'confirmed')->count(),
            'failed' => EventRegistrationLog::where('status', 'failed')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function clearCache()
    {
        Cache::flush();

        return back()->with('cache_cleared', true);
    }
}
