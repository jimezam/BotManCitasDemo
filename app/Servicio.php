<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
    ];
    
    /**
     * Obtiene las citas creadas reservando un servicio en particular
     */
    public function citas()
    {
        return $this->hasMany('App\Cita');
    }
}
