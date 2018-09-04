<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;

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
        $this->preguntarFecha();
    }

    public function saludo()
    {
        $this->say('Te voy a hacer algunas preguntas para agendar tu cita.');
    }

    public function preguntarFecha()
    {
        $this->ask("¿Para qué día deseas la cita? \n(dd/mm/aaaa)", function (Answer $answer) 
        {   
            if($this->validarFecha($answer->getText()))
            {
                $this->preguntarHora();
            }
            else
            {
                $this->say("Esa fecha parece ser incorrecta, por favor verifícala.");
                $this->preguntarFecha();
            }
        });
    }

    private function validarFecha($cadena)
    {
        // Procesar la fecha recibida para obtener sus partes
        $partes = explode ("/", $cadena);

        // Verificar que tenga tres partes (d/m/a)
        if(count($partes) != 3)
            return false;

        // Verifcar que sea una fecha válida
        $control = checkdate($partes[1], $partes[0], $partes[2]);

        if(!$control)
            return false;

        // Verificar que sea una fecha futura
        if (new \DateTime() >= new \DateTime("$partes[1]-$partes[0]-$partes[2]"))
            return false;

        return true;
    }
}
