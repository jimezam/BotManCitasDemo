<?php

use Illuminate\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Servicio::create(['nombre' => 'Latoneria']);
        App\Servicio::create(['nombre' => 'Pintura']);
        App\Servicio::create(['nombre' => 'Electrónica']);
        App\Servicio::create(['nombre' => 'Mecánica']);
        App\Servicio::create(['nombre' => 'Suspensión']);
        App\Servicio::create(['nombre' => 'Frenos']);
    }    
}
