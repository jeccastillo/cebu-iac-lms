<?php

namespace App\Events;

use App\Models\SystemAlert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SystemAlertBroadcasted implements ShouldBroadcast
{
    public string $action;
    public array $alert;

    public function __construct(string $action, SystemAlert $alert)
    {
        $this->action = $action;
        // Serialize model to array for transport
        $this->alert = $alert->toArray();
    }

    public function broadcastOn(): array
    {
        // Simple public channel; can add role/campus granular channels later
        return [new Channel('system.alerts')];
    }

    public function broadcastAs(): string
    {
        return 'system.alert';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'alert'  => $this->alert,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
