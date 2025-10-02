<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TypeProduct;


class TypeProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeProduct::create(['name' => 'Pieza']);
        TypeProduct::create(['name' => 'Gramos']);
        TypeProduct::create(['name' => 'Granel']);
    }
}