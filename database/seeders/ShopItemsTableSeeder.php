<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShopItem;

class ShopItemsTableSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'name' => 'VIP Mensal Básico',
                'description' => '🌟 DESTAQUE-SE NO SERVIDOR! Acesso exclusivo a itens cosméticos únicos, fogos de artifício espetaculares e comandos essenciais como /hat e /fix que transformarão completamente sua experiência de jogo. Renove mensalmente e mantenha seus privilégios!',
                'price' => 20.00,
            ],
            [
                'name' => 'VIP Mensal Aventureiro',
                'description' => '⚡ POTENCIALIZE SUA JORNADA! Domine o servidor com um chunkloader VIP exclusivo, efeitos de partículas deslumbrantes que farão todos olharem para você e comandos especiais que economizarão seu tempo. Sua aventura nunca mais será a mesma!',
                'price' => 35.00,
            ],
            [
                'name' => 'VIP Mensal Lendário',
                'description' => '👑 A EXPERIÊNCIA SUPREMA! Torne-se uma lenda com 5 chunkloaders VIP premium, acesso TOTAL a TODOS os cosméticos disponíveis e o conjunto COMPLETO de comandos especiais. Domine o servidor e desfrute do máximo que o jogo pode oferecer!',
                'price' => 75.00,
            ],
        ];

        foreach ($items as $item) {
            ShopItem::create($item);
        }
    }
}
