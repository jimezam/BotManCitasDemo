<?php

use App\Http\Controllers\BotManController;

$botman = resolve('botman');

/*
$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
*/

/*
$botman->middleware->received(new \App\Logger());
$botman->middleware->heard(new \App\Logger());
sending, received, and heard.
*/

// Inicio
//////////////////////////////////////////////////////////////////////////////

// Inicia una charla con el bot de citas

$botman->hears('/start', function ($bot) {

    // Obtener la información del usuario en sesión
    $user = $bot->getUser();
    $id = $user->getId();
    $driver = $bot->getDriver()->getName();
    $username = $user->getUsername() ?: "desconocido";
    $firstname = $user->getFirstName() ?: "desconocido";
    $lastname = $user->getLastName() ?: "desconocido";

    // Crear o actualizar la información del usuario en sesión
    $cliente = \App\Cliente::firstOrNew(array(
        'codigo' => $id,
        'driver' => $driver,
        'nombre_usuario' => $username,
        'nombres' => $firstname,
        'apellidos' => $lastname
    ));
    // $cliente->foo = Input::get('foo');
    $cliente->save();

    // Mostrar mensaje de bienvenida
    $bot->reply("Hola $firstname, soy el asistente para la creación de citas.");
});

// Servicios
//////////////////////////////////////////////////////////////////////////////

// Lista los servicios actuales

$botman->hears('Ver servicios|Servicios', function ($bot) {
    $bot->reply("Los servicios actuales son:");

    // Obtener los servicios almacenados en la base de datos
    $servicios = \App\Servicio::orderBy('nombre', 'asc')->get();
    
    // Mostrar los servicios uno por uno
    foreach($servicios as $servicio)
    {
        $bot->reply($servicio->nombre." (".$servicio->id.")");
    }
});

// Agrega un nuevo servicio

$botman->hears('Nuevo servicio|Crear servicio', function($bot) {
    $bot->startConversation(new App\Http\Conversations\ServicioAgregarConversacion);
});

// Citas
//////////////////////////////////////////////////////////////////////////////

// Crear una nueva cita

$botman->hears('Deseo agendar una cita|Nueva cita|Crear cita', function($bot) {
    $bot->startConversation(new App\Http\Conversations\CitaCrearConversacion);
});

// Listar las citas del cliente en sesión

$botman->hears('Listar mis citas|Listar citas|citas', function ($bot) {
    
    // Identificar al usuario en sesión dentro del sistema de mensajería
    $user = $bot->getUser();
    $id = $user->getId();

    // Indentificar la información del usuario como cliente
    $cliente = \App\Cliente::where('codigo', $id)->first();

    if($cliente == null)
    {
        $this->say("Lo siento, no pude buscar las citas debido a que no conozco al cliente: ". $id); 
        return;
    }

    // Identificar las citas pendientes del cliente
    $citas = \App\Cita::where('cliente_id', $cliente->id)
                ->whereDate('fecha', '>=', new \DateTime())
                ->orderBy('fecha', 'asc')
                ->get();
    
    // Listar las citas encontradas
    $bot->reply("Las citas que tienes pendientes son las siguientes:");

    foreach($citas as $cita)
    {
        $momento = new \DateTime($cita->fecha);

        $servicio = $cita->servicio->nombre;

        // Preparar el mensaje que se mostrará por cada cita
        $textoCita = "En " . $momento->format('d/m/Y') . 
                     " a las " . $momento->format('h:i') .
                     " para " . $servicio;

        $bot->reply($textoCita);
    }
});

// Fallback
//////////////////////////////////////////////////////////////////////////////

$botman->fallback(function ($bot) {
    $bot->reply("No entendí tu último mensaje :-(");
});

//////////////////////////////////////////////////////////////////////////////
