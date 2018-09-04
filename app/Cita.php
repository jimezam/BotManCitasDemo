<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cliente_id', 'servicio_id', 'fecha'
    ];
    
    /**
     * Obtiene el cliente que creó la cita
     */
    public function cliente()
    {
        return $this->belongsTo('App\Cliente');
    }

    /**
     * Obtiene el servicio que se reservó en la cita
     */
    public function servicio()
    {
        return $this->belongsTo('App\Servicio');
    }
}
