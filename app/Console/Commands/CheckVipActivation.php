<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckVipActivation extends Command
{
    /**
     * O nome e assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'vip:check-activation';

    /**
     * A descrição do comando do console.
     *
     * @var string
     */
    protected $description = 'Verifica e ativa o VIP para compras aprovadas';

    /**
     * Executa o comando do console.
     */
    public function handle()
    {
        $this->line('Iniciando verificação de ativação de VIP...');
        
        // Busca todas as compras aprovadas, independente da data
        $approvedPurchases = Purchase::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info('Total de compras aprovadas encontradas: '.$approvedPurchases->count());

        foreach ($approvedPurchases as $purchase) {
            $this->line("Processando compra ID: {$purchase->id}");
            
            // Busca o usuário dono da compra
            $user = User::find($purchase->user_id);
            
            if ($user) {
                $this->line("Usuário encontrado: ID {$user->id}");
                
                // Busca o nickname do usuário
                $nickname = $user->minecraft_id;
                
                if ($nickname) {
                    $this->line("Nickname do usuário: {$nickname}");
                    
                    // Monta o comando para adicionar o VIP temporário
                    $command = "lp user {$nickname} parent addtemp vip 30d";
                    
                    $this->line("Enviando comando: {$command}");
                    
                    // Envia o comando usando o SendJsonApiCommand
                    $this->call('minecraft:send-json-api-command', [
                        'jsonCommand' => $command
                    ]);
                    
                    // Atualiza o status da compra para 'closed'
                    $purchase->status = 'closed';
                    $purchase->save();
                    
                    $this->info("VIP ativado com sucesso para o usuário: {$nickname}");
                    $this->line("Status da compra atualizado para 'closed'");
                } else {
                    $this->error("Nickname não encontrado para o usuário ID: {$user->id}");
                }
            } else {
                $this->error("Usuário não encontrado para a compra ID: {$purchase->id}");
            }
        }
        
        $this->info('Verificação de ativação de VIP concluída com sucesso.');
    }
}