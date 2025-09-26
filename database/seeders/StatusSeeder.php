<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(['name' => 'vendido']);
        Status::create(['name' => 'existente']);
        Status::create(['name' => 'traspaso']);
        Status::create(['name' => 'dañado']);
        Status::create(['name' => 'faltante']);
        Status::create(['name' => 'salida']);
        Status::create(['name' => 'devolucion']);
    }
}