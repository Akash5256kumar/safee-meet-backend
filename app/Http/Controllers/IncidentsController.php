<?php

namespace App\Http\Controllers;

use App\Models\Incident;

class IncidentsController extends Controller
{
    public function index()
    {
        $activeSos = Incident::where('type', 'sos')->whereIn('status', ['open', 'investigating'])->count();
        $resolvedToday = Incident::where('status', 'resolved')->whereDate('resolved_at', today())->count();
        $underReview = Incident::where('status', 'investigating')->count();

        $totalCount = Incident::count();
        $resolvedCount = Incident::where('status', 'resolved')->count();
        $resolutionRate = $totalCount > 0 ? round(($resolvedCount / $totalCount) * 100, 1) : 0;

        $incidents = Incident::with(['reporter', 'meeting'])
            ->latest()
            ->paginate(20);

        return view('incidents.index', [
            'incidents' => $incidents,
            'activeSos' => $activeSos,
            'resolvedToday' => $resolvedToday,
            'underReview' => $underReview,
            'resolutionRate' => $resolutionRate,
        ]);
    }
}
