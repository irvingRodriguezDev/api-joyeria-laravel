<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // precios con precisión razonable
            $table->decimal('price_purchase', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            // porcentaje de descuento (ej: 15.50)
            $table->decimal('percent_discount', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
};