<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected static function booted()
    {
        static::updating(function (Ticket $ticket) {
            if ($ticket->isDirty('status') && $ticket->status === 'resolved') {
                $ticket->completed_at = now();
            }
        });
    }
}
