<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Property;
use App\Support\LeadPipeline;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_leads' => Lead::count(),
            'total_properties' => Property::count(),
            'conversion_rate' => 12.4,
            'avg_property_price' => number_format((float) Property::avg('price'), 2),
            'calls_today' => $this->callsMadeToday(),
            'talk_time_today' => $this->talkTimeToday(),
        ];

        $boardColumns = LeadPipeline::build();

        if ($boardColumns->isEmpty()) {
            $boardColumns = collect(['No Leads Yet' => collect()]);
        }

        return view('dashboard.index', compact('metrics', 'boardColumns'));
    }

    protected function callsMadeToday(): int
    {
        if (!Schema::hasTable('calls')) {
            return 0;
        }

        return (int) DB::table('calls')
            ->whereDate('created_at', Carbon::today())
            ->count();
    }

    protected function talkTimeToday(): int
    {
        if (!Schema::hasTable('calls') || !Schema::hasColumn('calls', 'duration')) {
            return 0;
        }

        return (int) DB::table('calls')
            ->whereDate('created_at', Carbon::today())
            ->sum('duration');
    }
}


