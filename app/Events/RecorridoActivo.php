<?php

namespace App\Events;

use App\Models\Recorrido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecorridoActivo implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $recorridoId;
    protected $usuarioId;
    protected $acabado;
    public function __construct(Recorrido $recorrido)
    {
        //
        $this->recorridoId = $recorrido['_id'];
        $this->usuarioId = $recorrido->usuario['_id'];
        $this->acabado = $recorrido['acabado'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Log::info('RecorridoActivo: ' . $this->recorridoId . 'Usuario: ' . $this->usuarioId);
        return new Channel('recorrido_' . $this->usuarioId);
    }

    public function broadcastAs()
    {
        return 'recorrido-activo';
    }

    public function broadcastWith()
    {
        return [
            'recorridoId' => $this->recorridoId,
            'usuarioId' => $this->usuarioId,
            'acabado' => $this->acabado,
        ];
    }

}
