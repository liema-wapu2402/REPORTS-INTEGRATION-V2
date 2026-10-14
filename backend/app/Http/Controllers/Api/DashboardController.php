<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/summary
     * Reads from the `reports` table (not the filesystem) so counts stay
     * correct even if files are later moved or deleted from disk.
     */
    public function summary()
    {
        $counts = Report::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $drivers = Report::query()
            ->whereNotNull('driver')
            ->selectRaw('driver, count(*) as total')
            ->groupBy('driver')
            ->pluck('total', 'driver');

        // Reports created per day for the last 7 days (oldest first).
        $perDay = Report::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($r) => $r->created_at->format('Y-m-d'))
            ->map->count();

        $activity = collect(range(6, 0))->map(function ($ago) use ($perDay) {
            $day = now()->subDays($ago);

            return ['date' => $day->format('Y-m-d'), 'label' => $day->format('D'), 'count' => $perDay[$day->format('Y-m-d')] ?? 0];
        })->values();

        $recent = Report::query()
            ->latest()
            ->take(10)
            ->get(['id', 'type', 'driver', 'title', 'filename', 'size_bytes', 'created_at']);

        return response()->json([
            'total_reports' => Report::count(),
            'total_size_bytes' => (int) Report::sum('size_bytes'),
            'by_type' => collect(Report::TYPES)->mapWithKeys(fn ($type) => [$type => $counts[$type] ?? 0]),
            'by_driver' => collect(['dompdf', 'snappy', 'puppeteer'])->mapWithKeys(fn ($d) => [$d => $drivers[$d] ?? 0]),
            'activity' => $activity,
            'recent' => $recent,
        ]);
    }
}
