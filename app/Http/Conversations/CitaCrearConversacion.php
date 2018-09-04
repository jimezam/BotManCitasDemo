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
                $this->nombreServicio = \App\Servicio::find($this->servicio)->nombre;

                $this->confirmar();
            } 
            else 
            {
                $this->say('Por favor elige un servicio de la lista.');
                $this->preguntarServicio();
            }
        });
    }

    public function confirmar()
    {
        $this->say("En " . $this->fecha . "\n\r".
                   " a las " . $this->hora .
                   " para " . $this->nombreServicio);
    
        $confirmacion = Question::create('¿Estás seguro de que deseas agendar esta cita?')
            ->addButtons([
                Button::create('Agendar')->value('si'),
                Button::create('Cancelar')->value('no')
            ]);

        $this->ask($confirmacion, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                $opcion = $answer->getValue();
                
                if($opcion == "si")
                {
                    $this->say('Entendido, voy a registrar la reserva.');
                    $this->registrarCita();
                }

                if($opcion == "no")
                {
                    $this->say('Entendido, cancelaré la solicitud.');
                }
            } else {
                $this->say('Por favor elige una opción de la lista.');
                $this->confirmar();
            }
        });
    }

    public function registrarCita()
    {
        // Identificar al usuario en sesión
        $id = $this->bot->getUser()->getId();
        $cliente = \App\Cliente::where('codigo', $id)->first();

        if($cliente == null)
        {
            $this->say("Lo siento, no pude registrar la cita debido a que no conozco al cliente: ". $id); 
            return;
        }

        // Preparar la fecha de la cita a registrar
        $moment = \DateTime::createFromFormat('d/m/Y h:i', $this->fecha . ' ' . $this->hora);
        // $moment->format('Y-m-d')

        // Registra la cita
        $control = \App\Cita::create([
            'cliente_id' => $cliente->id,
            'servicio_id' => $this->servicio,
            'fecha' => $moment
        ]);
    }
}
