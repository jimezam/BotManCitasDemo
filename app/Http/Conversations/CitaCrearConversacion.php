<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;

class CitaCrearConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->saludo();
    }

    public function saludo()
    {
        $this->say('Te voy a hacer algunas preguntas para agendar tu cita.');
    }

    public function preguntarFecha()
    {}
}
