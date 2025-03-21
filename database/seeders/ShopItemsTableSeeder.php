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
                'name' => 'Diamante',
                'description' => 'Um valioso cristal de diamante.',
                'price' => 100, // Preço hipotético
            ],
            [
                'name' => 'Esmeralda',
                'description' => 'Uma preciosa esmeralda lapidada.',
                'price' => 90,
            ],
            [
                'name' => 'Rubi',
                'description' => 'Um raro rubi lapidado.',
                'price' => 80,
            ],
            [
                'name' => 'Pérola do Dragão',
                'description' => 'Uma pérola mágica do dragão ancestral.',
                'price' => 120,
            ],
            [
                'name' => 'Barra de Ouro Encantada',
                'description' => 'Uma barra de ouro misteriosa e encantada.',
                'price' => 150,
            ],
            [
                'name' => 'Barra de Ferro Celestial',
                'description' => 'Uma barra de ferro forjada nas estrelas.',
                'price' => 130,
            ],
            [
                'name' => 'Pedra da Eternidade',
                'description' => 'Uma pedra mágica que emana energia etérea.',
                'price' => 200,
            ],
            [
                'name' => 'Espada Lendária',
                'description' => 'Uma espada lendária de poder inigualável.',
                'price' => 300,
            ],
            [
                'name' => 'Armadura Épica',
                'description' => 'Uma armadura épica feita de materiais raros.',
                'price' => 280,
            ],
            [
                'name' => 'Poção de Invulnerabilidade',
                'description' => 'Uma poção que concede temporariamente invulnerabilidade.',
                'price' => 160,
            ],
            [
                'name' => 'Amuleto da Sabedoria',
                'description' => 'Um amuleto que aumenta a sabedoria do portador.',
                'price' => 110,
            ],
            [
                'name' => 'Mapa do Tesouro',
                'description' => 'Um mapa que leva a um tesouro escondido.',
                'price' => 170,
            ],
            [
                'name' => 'Fragmento de Estrela',
                'description' => 'Um fragmento brilhante de uma estrela cadente.',
                'price' => 140,
            ],
            [
                'name' => 'Pergaminho de Encantamento Divino',
                'description' => 'Um pergaminho que contém encantamentos divinos.',
                'price' => 220,
            ],
            [
                'name' => 'Essência Arcana',
                'description' => 'Uma essência mágica de origem desconhecida.',
                'price' => 180,
            ],
            [
                'name' => 'Arco Celestial',
                'description' => 'Um arco que dispara flechas energéticas.',
                'price' => 190,
            ],
            [
                'name' => 'Livro de Feitiços Ancestrais',
                'description' => 'Um livro com feitiços ancestrais poderosos.',
                'price' => 250,
            ],
            [
                'name' => 'Elixir da Vida',
                'description' => 'Um elixir que concede vitalidade e cura.',
                'price' => 210,
            ],
            [
                'name' => 'Relíquia do Herói',
                'description' => 'Uma relíquia carregada de memórias heroicas.',
                'price' => 230,
            ],
            [
                'name' => 'Cristal de Energia',
                'description' => 'Um cristal que emite uma energia radiante.',
                'price' => 200,
            ],
            [
                'name' => 'Chave do Portal Dimensional',
                'description' => 'Uma chave que permite acesso a dimensões secretas.',
                'price' => 240,
            ],
            [
                'name' => 'Relógio do Tempo',
                'description' => 'Um relógio que controla o fluxo do tempo.',
                'price' => 260,
            ],
            [
                'name' => 'Lágrima do Dragão',
                'description' => 'Uma lágrima do coração de um dragão lendário.',
                'price' => 290,
            ],
            [
                'name' => 'Pó de Fada Luminosa',
                'description' => 'Um pó mágico que brilha com luzes encantadoras.',
                'price' => 180,
            ],
            [
                'name' => 'Pena de Grifo',
                'description' => 'Uma pena rara de uma majestosa criatura grifo.',
                'price' => 170,
            ],
            [
                'name' => 'Fragmento de Alma',
                'description' => 'Um fragmento que contém a essência de uma alma.',
                'price' => 270,
            ],
            [
                'name' => 'Cristal de Aura',
                'description' => 'Um cristal que emana uma aura mística.',
                'price' => 230,
            ],
            [
                'name' => 'Pérola da Lua',
                'description' => 'Uma pérola que captura o brilho suave da lua.',
                'price' => 210,
            ],
            [
                'name' => 'Joia do Vórtice',
                'description' => 'Uma joia que canaliza poderosos ventos de vórtice.',
                'price' => 280,
            ],
            [
                'name' => 'Poção de Transformação',
                'description' => 'Uma poção que transforma o jogador temporariamente.',
                'price' => 150,
            ],
        ];

        foreach ($items as $item) {
            ShopItem::create($item);
        }
    }
}
