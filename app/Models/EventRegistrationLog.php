<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistrationLog extends Model
{
    protected $fillable = [
        'event_id', 'event_slug', 'event_title',
        'ticket_id', 'ticket_name', 'email', 'first_name',
        'last_name', 'phone', 'status', 'amount', 'currency',
        'transaction_id', 'check_in_token', 'raw_response',
    ];

    protected function casts(): array
    {
        return ['raw_response' => 'array'];
    }
}
