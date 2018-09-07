<?php

namespace App\Http\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

use BotMan\BotMan\Messages\Conversations\Conversation;

class ServicioAgregarConversacion extends Conversation
{
    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        if(!$this->isUserAdmin())
        {
            $this->say("Oh oh, lo siento, esta funcionalidad sólo está disponible para el administrador");
            return;
        }

        $this->saludo();
        $this->preguntarNombre();

        /*
        $this->say("hola", ["445956852", "600357848"], TelegramDriver::class);
        */

        /*
        $this->say("hola 4", "445956852", TelegramDriver::class);
        $this->say("hola 6", "600357848", TelegramDriver::class);
        */

        /*
        $this->say("hola: 44", "jimezam", TelegramDriver::class);
        $this->say("hola: 60", "JorgeIMeza", TelegramDriver::class);
        */
    }

    private function isUserAdmin()
    {
        if($this->bot->getUser()->getUsername() == env('ADMIN_LIST'))
            return true;

        return false;
    }
    
    public function saludo()
    {
        $this->say('Te voy a hacer algunas preguntas para agregar el nuevo servicio.');
    }

    public function preguntarNombre()
    {
        $this->ask("¿Cuál será el nombre del nuevo servicio?", function (Answer $answer) 
        {   
            $nombre = $answer->getText();

            $control = $this->agregarServicio($nombre);

            if($control)
            {
                $this->contarleATodos("A partir de este momento tenemos un nuevo servicio: '".$nombre."'.");
            }
            else
                $this->say("El servicio '$nombre' NO pudo ser agregado!");
        });
    }

    public function agregarServicio($nombre)
    {
        $cantidad = \App\Servicio::where('nombre', $nombre)->count();

        // Verifica si existía un servicio con el mismo nombre
        if($cantidad == 0)
        {
            // Registra el servicio
            $control = \App\Servicio::create([
                'nombre' => $nombre,
            ]);

            if(!$control)
                return false;

            return true;
        }

        return false;
    }

    private function contarleATodos($mensaje)
    {
        // $id = $this->bot->getUser()->getId();

        $clientes = \App\Cliente::all();

        foreach($clientes as $cliente)
        {
            $para = $cliente->codigo;
    
            // $this->say($mensaje, $para);             // Does not work
            $this->bot->say($mensaje, $para);
        }
    }

    public function stopsConversation(IncomingMessage $message)
	{
		if ($message->getText() == 'cancelar') {
			return true;
		}

		return false;
	}
}
