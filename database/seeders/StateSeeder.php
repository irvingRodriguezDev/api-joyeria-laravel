<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Aguascalientes'],
            ['name' => 'Baja California'],
            ['name' => 'Baja California Sur'],
            ['name' => 'Campeche'],
            ['name' => 'Chiapas'],
            ['name' => 'Chihuahua'],
            ['name' => 'Coahuila'],
            ['name' => 'Colima'],
            ['name' => 'Durango'],
            ['name' => 'Guanajuato'],
            ['name' => 'Guerrero'],
            ['name' => 'Hidalgo'],
            ['name' => 'Jalisco'],
            ['name' => 'Estado de México'],
            ['name' => 'CDMX'],
            ['name' => 'Michoacán'],
            ['name' => 'Morelos'],
            ['name' => 'Nayarit'],
            ['name' => 'Nuevo León'],
            ['name' => 'Oaxaca'],
            ['name' => 'Puebla'],
            ['name' => 'Querétaro'],
            ['name' => 'Quintana Roo'],
            ['name' => 'San Luis Potosí'],
            ['name' => 'Sinaloa'],
            ['name' => 'Sonora'],
            ['name' => 'Tabasco'],
            ['name' => 'Tamaulipas'],
            ['name' => 'Tlaxcala'],
            ['name' => 'Veracruz'],
            ['name' => 'Yucatán'],
            ['name' => 'Zacatecas'],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }
}