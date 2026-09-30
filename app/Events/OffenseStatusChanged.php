<?php

namespace App\Events;

use App\Models\Offense;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OffenseStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offense;

    /**
     * Create a new event instance.
     */
    public function __construct(Offense $offense)
    {
        $this->offense = $offense;
    }
}
