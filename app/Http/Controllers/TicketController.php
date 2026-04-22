<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    private function handleImageUpload($request)
    {
        if (! $request->hasFile('image_url')) {
            return null;
        }
        $cloudinary = new Cloudinary;
        $filePath = $request->file('image_url')->getRealPath();
        $uploadProcess = $cloudinary->uploadApi()->upload($filePath, ['folder' => 'ticket_laporan']);

        return $uploadProcess['secure_url'];
    }

    public function arsip(Request $request)
    {
        $user = auth()->user();

        $ticketsQuery = $user->ticket();

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

        return Inertia::render('user/Arsip', [
            'tickets' => $ticketsQuery->with('user:id,name')->where('status', 'resolved')->latest()->paginate(8),
            'filters' => $request->only(['search', 'priority']),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $ticketsQuery = $user->ticket();

        $ticketsQuery->when($request->filled('search'), function ($query) use ($request) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        })->when($request->filled('status') && $request->status !== '*', function ($query) use ($request) {
            $query->where('status', $request->status);
        })->when($request->filled('priority') && $request->priority !== '*', function ($query) use ($request) {
            $query->where('priority', $request->priority);
        });

        $ticketsQuery->withCount(['ticketReply as unread_replies_count' => function ($query) use ($user) {
            $query->where('user_id', '!=', $user->id)
                ->where('is_read', false);
        }]);

        $tickets = $ticketsQuery->latest()->where('status', '!=', 'resolved')->paginate(8)->withQueryString();

        $renderData = [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'priority']),
        ];

        return Inertia::render('user/Ticket', $renderData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request)
    {
        $data = $request->validated();

        if ($imageUrl = $this->handleImageUpload($request)) {
            $data['image_url'] = $imageUrl;
        }

        $request->user()->ticket()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Laporan berhasil dibuat']);

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        if ($imageUrl = $this->handleImageUpload($request)) {
            $data['image_url'] = $imageUrl;
        }
        $ticket->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Laporan berhasil diupdate']);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
