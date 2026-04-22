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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $ticketsQuery = $user->role === 'admin' ? Ticket::query() : $user->ticket();

        if ($request->filled('search')) {
            $search = $request->search;
            $ticketsQuery->where(function($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $ticketsQuery->latest()->paginate(8)->withQueryString();

        $renderData = [
            'tickets' => $tickets,
            'filters' => $request->only(['search']),
        ];

        if ($user->role === 'admin') {
            return Inertia::render('admin/Dashboard', $renderData);
        }

        return Inertia::render('user/Dashboard', $renderData);
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
