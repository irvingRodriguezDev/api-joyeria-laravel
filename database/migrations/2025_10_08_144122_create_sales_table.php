<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('folio')->default(1);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_out', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'folio'], 'sales_branch_folio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};