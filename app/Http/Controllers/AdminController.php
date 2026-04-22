<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'date' => now()->year.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'-01',
                'laporan' => Ticket::whereYear('created_at', now()->year)->whereMonth('created_at', $i)->count(),
            ];
        }

        $lastMonth = now()->subMonth();
        $thisMonthCount = Ticket::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $lastMonthCount = Ticket::whereYear('created_at', $lastMonth->year)->whereMonth('created_at', $lastMonth->month)->count();

        $trendingPercentage = 0;
        if ($lastMonthCount > 0) {
            $trendingPercentage = (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100;
        } elseif ($thisMonthCount > 0) {
            $trendingPercentage = 100;
        }

        $statusCounts = Ticket::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return Inertia::render('admin/Dashboard', [
            'chartData' => $chartData,
            'trendingPercentage' => round($trendingPercentage, 1),
            'statusCounts' => $statusCounts,
        ]);
    }

    public function index()
    {
        return Inertia::render('admin/Ticket');
    }
}
