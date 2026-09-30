<?php

namespace App\Events\CaseEvents;

use App\Models\TribunalCase;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintFiled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TribunalCase $case,
        public User $complainant,
        public string $description
    ) {}
}
