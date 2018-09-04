<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;

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
                $this->fecha = $answer->getText();
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

    public function preguntarHora()
    {
        $this->ask("¿Para qué hora deseas la cita? \n(hh:mm)", function (Answer $answer) 
        { 
            if($this->validarHora($answer->getText()))
            {
                $this->hora = $answer->getText();
                $this->preguntarServicio();
            }
            else
            {
                $this->say("Esa hora parece ser incorrecta, por favor verifícala.");
                $this->preguntarHora();
            }
        });
    }

    private function validarHora($cadena)
    {
        // 24h: preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $cadena)

        return preg_match("/^(?:1[012]|0[0-9]):[0-5][0-9]$/", $cadena);
    }

    public function preguntarServicio()
    {
        $servicios = \App\Servicio::orderBy('nombre', 'asc')->get();

        $botones = [];

        foreach($servicios as $servicio)
        {
            $botones[] = Button::create($servicio->nombre)->value($servicio->id);
        }

        $cualServicio = Question::create('¿Qué servicio deseas agendar?')
            ->addButtons($botones);

        $this->ask($cualServicio, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) 
            {
                $this->servicio = $answer->getValue();
                $nombreServicio = \App\Servicio::find($this->servicio)->nombre;

                $this->confirmar();
            } 
            else 
            {
                $this->say('Por favor elige un servicio de la lista.');
                $this->preguntarServicio();
            }
        });
    }
}
