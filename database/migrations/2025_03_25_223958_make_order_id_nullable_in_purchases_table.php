<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações.
     */
    public function up(): void
    {
        Schema::dropIfExists('purchases');
        
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('order_id')->nullable();
            $table->string('customer_email');
            $table->decimal('amount', 10, 2);
            $table->string('product_type')->nullable();
            $table->string('status')->nullable();
            $table->string('shop_item_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
