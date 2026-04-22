<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class TicketReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Ticket $ticket, Request $request)
    {
        if ($request->boolean('mark_read')) {
            $ticket->ticketReply()
                ->where('user_id', '!=', $request->user()->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $replies = $ticket->ticketReply()
            ->with(['user' => function($query) {
                $query->select('id', 'name', 'role');
            }])
            ->oldest()
            ->get();

        return response()->json($replies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Ticket $ticket, Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000'
        ]);

        $reply = $ticket->ticketReply()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'is_read' => false
        ]);

        $reply->load(['user' => function($query) {
            $query->select('id', 'name', 'role');
        }]);

        return response()->json($reply);
    }
}
