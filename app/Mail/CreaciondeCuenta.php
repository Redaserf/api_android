<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreaciondeCuenta extends Mailable
{
    use Queueable, SerializesModels;

    public $correo;
    public $codigo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($correo, $codigo)
    {
        //
        $this->correo = $correo;
        $this->codigo = $codigo;
    }

    public function build()
    {
        return $this->view('Emails.notifiacioncuenta')
                    ->subject('Verificar Cuenta')
                    ->with(['correo' => $this->correo,
                'url'=>$this->codigo]);
    }



    
}
