<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected static function booted()
    {
        static::updating(function (Ticket $ticket) {
            if ($ticket->isDirty('status') && $ticket->status === 'resolved') {
                $ticket->completed_at = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketReply()
    {
        return $this->hasMany(TicketReply::class);
    }
}
