<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Executa as migrações.
     */
    public function up(): void
    {
        // Adiciona uma nova coluna temporária para armazenar o valor como string
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('shop_item_id_temp')->nullable()->after('shop_item_id');
        });

        // Copia os dados da coluna antiga para a nova
        DB::table('purchases')->update(['shop_item_id_temp' => DB::raw('shop_item_id')]);

        // Remove a coluna antiga
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('shop_item_id');
        });

        // Renomeia a coluna temporária para o nome original
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('shop_item_id_temp', 'shop_item_id');
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        // Adiciona uma nova coluna temporária para armazenar o valor como unsignedBigInteger
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_item_id_temp')->nullable()->after('shop_item_id');
        });

        // Copia os dados da coluna atual para a nova
        DB::table('purchases')->update(['shop_item_id_temp' => DB::raw('shop_item_id')]);

        // Remove a coluna atual
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('shop_item_id');
        });

        // Renomeia a coluna temporária para o nome original
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('shop_item_id_temp', 'shop_item_id');
        });
    }
};
