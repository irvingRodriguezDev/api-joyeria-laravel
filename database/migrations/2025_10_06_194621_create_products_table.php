<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('description');
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('line_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('status_id')->default(2)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('weight', 10, 2)->nullable();
            $table->text('observations')->nullable();
            $table->decimal('price_purchase', 10, 2);
            $table->decimal('price', 10, 2);
            $table->decimal('price_with_discount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};