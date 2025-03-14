<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewClientPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param $client
     * @param $password
     */
    public $client;
    public $password;

    //Class constructor
    public function __construct($client, $password)
    {
        $this->client = $client;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Client Password',
        );
    }

    public function build(){
        return $this->view('emails.new-client-password')
            ->subject('Tu nueva contraseña')
            ->with([
                'clientName' => $this->client->name,
                'password' => $this->password
            ]);
    }
}
