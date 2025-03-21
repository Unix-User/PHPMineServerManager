<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assinaturas_vip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Chave estrangeira para a tabela de usuários
            $table->foreignId('shop_item_id')->constrained('shop_items'); // Chave estrangeira para a tabela de itens da loja
            $table->dateTime('data_inicio');
            $table->dateTime('data_termino');
            $table->boolean('ativa')->default(true); // Campo para indicar se a assinatura está ativa ou não
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assinaturas_vip');
    }
};
