<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;




class PagoConfirmado implements ShouldBroadcast
{
    use SerializesModels;

    public $ventaId;

    public function __construct($ventaId)
    {
        $this->ventaId = $ventaId;
    }

    public function broadcastOn()
    {
        return new Channel('pagos');
    }

    public function broadcastWith()
    {
        return [
            'ventaId' => $this->ventaId,
            'message' => '¡Pago confirmado!',
        ];
    }
}