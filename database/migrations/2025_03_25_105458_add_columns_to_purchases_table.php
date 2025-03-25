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
            if (Schema::hasColumn('purchases', 'player_id')) {
                $table->dropColumn('player_id');
            }

            if (!Schema::hasColumn('purchases', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('purchases', 'order_id')) {
                $table->string('order_id')->unique()->after('user_id');
            }

            if (!Schema::hasColumn('purchases', 'customer_email')) {
                $table->string('customer_email')->after('order_id');
            }

            if (!Schema::hasColumn('purchases', 'amount')) {
                $table->decimal('amount', 10, 2)->after('customer_email');
            }

            if (!Schema::hasColumn('purchases', 'product_type')) {
                $table->string('product_type')->nullable()->after('amount');
            }

            if (!Schema::hasColumn('purchases', 'status')) {
                $table->string('status')->nullable()->after('product_type');
            }

            if (Schema::hasColumn('purchases', 'user_id') && !Schema::hasColumn('purchases', 'shop_item_id') ) {
                 if (!Schema::hasColumn('purchases', 'shop_item_id')) {
                    $table->foreign('shop_item_id')
                        ->references('id')
                        ->on('shop_items')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }
            } else if (!Schema::hasColumn('purchases', 'user_id') && Schema::hasColumn('purchases', 'shop_item_id') ) {
                if (!Schema::hasColumn('purchases', 'user_id')) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }
            }
             else {
                if (!Schema::hasColumn('purchases', 'user_id')) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }

                if (!Schema::hasColumn('purchases', 'shop_item_id')) {
                    $table->foreign('shop_item_id')
                        ->references('id')
                        ->on('shop_items')
                        ->onDelete('set null')
                        ->onUpdate('cascade');
                }
            }
        });
    }

    /**
     * Reverte as migrações.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
            if (Schema::hasColumn('purchases', 'shop_item_id')) {
                $table->dropForeign(['shop_item_id']);
            }

            $columnsToRemove = [
                'user_id',
                'order_id',
                'customer_email',
                'amount',
                'product_type',
                'status'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    $table->dropColumn($column);
                }
            }


            if (!Schema::hasColumn('purchases', 'player_id')) {
                $table->unsignedBigInteger('player_id')->after('id');
            }
        });
    }
};
