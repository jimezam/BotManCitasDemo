<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

/*
$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('Start conversation', BotManController::class.'@startConversation');
*/

$botman->hears('Ver servicios', function ($bot) {
    $bot->reply("Los servicios actuales son:");

    $servicios = \App\Servicio::orderBy('nombre', 'asc')->get();
    foreach($servicios as $servicio)
    {
        $bot->reply($servicio->nombre." (".$servicio->id.")");
    }
});