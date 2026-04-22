<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function arsip(Request $request)
    {
        $ticketsQuery = Ticket::query();

        $ticketsQuery->when($request->filled('search'), function ($query) use ($request) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        });

        $ticketsQuery->when($request->filled('priority') && $request->priority !== '*', function ($query) use ($request) {
            $query->where('priority', $request->priority)->where('status', 'resolved');
        });

        return Inertia::render('admin/Arsip', [
            'tickets' => $ticketsQuery->with('user:id,name')->where('status', 'resolved')->latest()->paginate(8),
            'filters' => $request->only(['search', 'priority']),
        ]);
    }

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

    public function index(Request $request)
    {
        $ticketsQuery = Ticket::query();

        $ticketsQuery->when($request->filled('search'), function ($query) use ($request) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
        });

        $ticketsQuery->when($request->filled('status') && $request->status !== '*', function ($query) use ($request) {
            $query->where('status', $request->status);
        });

        $ticketsQuery->when($request->filled('priority') && $request->priority !== '*', function ($query) use ($request) {
            $query->where('priority', $request->priority);
        });

        return Inertia::render('admin/Ticket', [
            'tickets' => $ticketsQuery->with('user:id,name')->where('status', '!=', 'resolved')->latest()->paginate(8),
            'filters' => $request->only(['search', 'status', 'priority']),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:in_progress,resolved,rejected',
        ]);

        $ticket->update($request->only('status'));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status laporan berhasil diperbarui',
        ]);

        return redirect()->back();
    }
}
