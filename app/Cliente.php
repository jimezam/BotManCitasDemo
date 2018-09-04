<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codigo', 'nombre_usuario', 'nombres', 'apellidos'
    ];

    /**
     * Obtiene las citas creadas por un cliente
     */
    public function citas()
    {
        return $this->hasMany('App\Cita');
    }
}
