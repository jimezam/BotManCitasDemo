<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

/*
$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
*/

// Servicios
//////////////////////////////////////////////////////////////////////////////

$botman->hears('Ver servicios', function ($bot) {
    $bot->reply("Los servicios actuales son:");

    // Obtener los servicios almacenados en la base de datos
    $servicios = \App\Servicio::orderBy('nombre', 'asc')->get();
    
    // Mostrar los servicios uno por uno
    foreach($servicios as $servicio)
    {
        $bot->reply($servicio->nombre." (".$servicio->id.")");
    }
});

// Citas
//////////////////////////////////////////////////////////////////////////////

/*
// Crear una cita para el 1 de abril de 2018 a las 11 am

$botman->hears('Crear una cita para el {dia} de {mes} de {ano} a las {hora} para {servicio}', 
    function ($bot, $dia, $mes, $ano, $hora, $servicio) {

    // Convertir el nombre del mes a su correspondiente 
    // índice: abril -> 04
    $mesNumerico = (new \DateTime($mes))->format('m');

    // Almacenar la fecha y hora de la cita solicitada
    $this->date = new \DateTime("$dia/$mesNumerico/$ano");
    $this->time = new \DateTime("$hora");
});
*/

/*
$bot->reply($this->date->format('d/m/Y'));
$bot->reply($this->time->format('H:i'));

//    $bot->reply($date);

$bot->reply($date);    
// (confirmar)
// (realizar)
*/

// Crear una nueva cita (proceso completo)

$botman->hears('Deseo agendar una cita|Nueva cita|Crear cita', function($bot) {
    $bot->startConversation(new App\Http\Conversations\CitaCrearConversacion);
});

//////////////////////////////////////////////////////////////////////////////
