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
// $this->preguntarHora();
$this->say("BIEN");
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

        $ahora = time();
        $fecha = mktime(23, 59, 59, intval($partes[1]), intval($partes[0]), intval($partes[2]));

        // Verificar que sea una fecha futura
        if ($fecha < $ahora)
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
        // Consultar los servicios disponibles en la base de datos
        $servicios = \App\Servicio::orderBy('nombre', 'asc')->get();

        // Preparar los botones de acuerdo con los servicios existentes
        $botones = [];

        foreach($servicios as $servicio)
        {
            $botones[] = Button::create($servicio->nombre)->value($servicio->id);
        }

        // Crear la "pregunta"
        $cualServicio = Question::create('¿Qué servicio deseas agendar?')
            ->addButtons($botones);

        // Presentar la pregunta al usuario
        $this->ask($cualServicio, function (Answer $answer) {
            // Si el usuario utilizó los botones para responder ...

            if ($answer->isInteractiveMessageReply()) 
            {
                $this->servicio = $answer->getValue();
                $this->nombreServicio = \App\Servicio::find($this->servicio)->nombre;

                $this->confirmar();
            } 
            else 
            {
                // Si el usuario digitó su respuesta como texto

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
        $momento = \DateTime::createFromFormat('d/m/Y h:i', $this->fecha . ' ' . $this->hora);
        // $momento->format('Y-m-d')

        // Registra la cita
        $control = \App\Cita::create([
            'cliente_id' => $cliente->id,
            'servicio_id' => $this->servicio,
            'fecha' => $momento
        ]);
    }
}
