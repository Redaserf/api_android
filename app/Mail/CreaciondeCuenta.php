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
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($correo,$url)
    {
        //
        $this->correo = $correo;
        $this->url = $url;
    }

    public function build()
    {
        return $this->view('emails.notifiacioncuenta')
                    ->subject('Creacion de Cuenta')
                    ->with(['correo' => $this->correo,
                'url'=>$this->url]);
    }



    
}
