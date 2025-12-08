<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('last_branch_id'); // sucursal origen
            $table->unsignedBigInteger('new_branch_id');  // sucursal destino

            // 1 enviado, 2 aceptado, 3 rechazado, 4 cancelado
            $table->tinyInteger('status')->default(1); 

            $table->unsignedBigInteger('user_origin_id');       // usuario que generó el envío
            $table->unsignedBigInteger('user_destination_id')->nullable(); // usuario que aceptó/rechazó

            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('last_branch_id')->references('id')->on('branches');
            $table->foreign('new_branch_id')->references('id')->on('branches');
            $table->foreign('user_origin_id')->references('id')->on('users');
            $table->foreign('user_destination_id')->references('id')->on('users');
        });
    }

    public function down() {
        Schema::dropIfExists('transfers');
    }
};