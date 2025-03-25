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
        Schema::table('purchases', function (Blueprint $table) {
            // Remove a coluna player_id que não será mais utilizada
            $table->dropColumn('player_id');
            
            // Adiciona as novas colunas com as mesmas especificações da tabela original
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('order_id')->unique()->after('user_id');
            $table->string('customer_email')->after('order_id');
            $table->decimal('amount', 10, 2)->after('customer_email');
            $table->string('product_type')->nullable()->after('amount');
            $table->string('status')->nullable()->after('product_type');

            // Adiciona chaves estrangeiras com restrições de integridade
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('shop_item_id')
                ->references('id')
                ->on('shop_items')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Remove as chaves estrangeiras
            $table->dropForeign(['user_id']);
            $table->dropForeign(['shop_item_id']);
            
            // Remove as colunas adicionadas
            $table->dropColumn([
                'user_id',
                'order_id',
                'customer_email',
                'amount',
                'product_type',
                'status'
            ]);
            
            // Restaura a coluna player_id
            $table->unsignedBigInteger('player_id')->after('id');
        });
    }
};
