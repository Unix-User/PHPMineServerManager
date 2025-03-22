<?php

namespace App\Http\Controllers;

use App\Models\AccountLinkRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccountLinkController extends Controller
{
    public function sendConfirmationEmail(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|min:3|max:16',
        ]);

        $nickname = $request->input('nickname');
        $user = auth()->user();
        $token = Str::uuid();

        try {
            $accountLinkRequest = new AccountLinkRequest();
            $accountLinkRequest->user_id = $user ? $user->id : null;
            $accountLinkRequest->nickname = $nickname;
            $accountLinkRequest->token = $token;
            $accountLinkRequest->expires_at = now()->addHours(24);
            $accountLinkRequest->save();

            $confirmationUrl = route('account.link.confirm', ['token' => $token]);
            $message = '[{"text":"§4§l⚠ ATENÇÃO! VINCULAÇÃO DE CONTA ⚠\n","hoverEvent":{"action":"show_text","value":"§7Sistema de segurança de contas"}},{"text":"§8• §r§eSolicitação para o e-mail: §7' . substr($user->email, 0, 5) . '*****@...\n","color":"yellow","hoverEvent":{"action":"show_text","value":"§aE-mail mascarado por segurança\n§fOriginal: ' . $user->email . '"}},{"text":"§8• §r§eStatus: §6Pendente de confirmação\n","hoverEvent":{"action":"show_text","value":"§cAção requerida pelo usuário"}},{"text":"§4§lMEDIDAS DE SEGURANÇA:\n","clickEvent":{"action":"open_url","value":"https://linkseguro.com/politica-seguranca"}},{"text":"§71. §cSe §nNÃO§r§c reconhece esta ação:\n   §8→ §6Ignore esta mensagem!\n","hoverEvent":{"action":"show_text","value":"§4Relate atividades suspeitas\n§7/cliente-servidor reportar"}},{"text":"§72. §aSe solicitou esta vinculação:\n   §8→ ","color":"green"},{"text":"[§2§lCONFIRMAR AGORA§r]","color":"green","bold":true,"clickEvent":{"action":"open_url","value":"' . $confirmationUrl . '"},"hoverEvent":{"action":"show_text","value":"§aClique para confirmar\n§7Expira em: 24 horas\n§8URL: ' . $confirmationUrl . '"}},{"text":"\n\n§8§fEsta solicitação é válida por 24 horas","italic":true}]';
            $command = "tellraw {$nickname} {$message}";

            Artisan::call('minecraft:send-json-api-command', [
                'jsonCommand' => $command
            ]);

            return back()->with([
                'status' => 'Email de confirmação enviado com sucesso para ' . $nickname
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'nickname' => 'Erro ao salvar solicitação de vinculação: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'nickname' => 'Erro ao enviar email de confirmação: ' . $e->getMessage()
            ]);
        }
    }

    public function addVipToUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:16'
        ]);

        $username = $request->input('username');
        $command = "lp user {$username} parent addtemp vip 30d";

        try {
            Artisan::call('minecraft:send-json-api-command', [
                'command' => $command
            ]);

            return response()->json([
                'success' => true,
                'message' => 'VIP adicionado com sucesso para o usuário ' . $username
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar VIP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirm(string $token)
    {
        $linkRequest = AccountLinkRequest::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$linkRequest) {
            return "<script>window.close();</script>";
        }

        if (!auth()->check() && $linkRequest->user_id) {
            $user = User::find($linkRequest->user_id);
            if ($user) {
                auth()->login($user);
            }
        }

        $user = auth()->user();
        if (!$user) {
            return "<script>window.close();</script>";
        }

        // Atualiza o nickname do usuário
        User::where('id', $user->id)->update([
            'minecraft_id' => $linkRequest->nickname
        ]);
        
        // Adiciona o usuário ao grupo jogador do LuckPerms
        $lpCommand = "lp user {$linkRequest->nickname} parent add jogador";
        Artisan::call('minecraft:send-json-api-command', [
            'jsonCommand' => $lpCommand
        ]);

        // Envia mensagem de confirmação no Minecraft
        $command = "tellraw {$linkRequest->nickname} {\"text\":\"Sua conta foi vinculada com sucesso!\",\"color\":\"green\"}";
        Artisan::call('minecraft:send-json-api-command', [
            'jsonCommand' => $command
        ]);

        // Envia mensagem de confirmação no Minecraft
        $command = "tellraw {$linkRequest->nickname} {\"text\":\"Sua conta foi vinculada com sucesso!\",\"color\":\"green\"}";
        Artisan::call('minecraft:send-json-api-command', [
            'jsonCommand' => $command
        ]);

        $linkRequest->delete();

        return "<script>window.close();</script>";
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = auth()->user();
        if (!$user || !$user->minecraft_id) {
            return back()->withErrors([
                'password' => 'Usuário não autenticado ou conta não vinculada'
            ]);
        }

        $nickname = $user->minecraft_id;
        $password = $request->input('password');

        try {
            $command = "hopecommander nlogin changepass {$nickname} {$password}";
            
            Artisan::call('minecraft:send-json-api-command', [
                'jsonCommand' => $command
            ]);

            // Envia mensagem de confirmação no Minecraft
            $messageCommand = "tellraw {$nickname} {\"text\":\"Sua senha foi alterada com sucesso!\",\"color\":\"green\"}";
            Artisan::call('minecraft:send-json-api-command', [
                'jsonCommand' => $messageCommand
            ]);

            return back()->with('status', 'Senha redefinida com sucesso para o usuário ' . $nickname);

        } catch (\Exception $e) {
            return back()->withErrors([
                'password' => 'Erro ao redefinir senha: ' . $e->getMessage()
            ]);
        }
    }

    public function unlinkAccount(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->minecraft_id) {
            return back()->withErrors([
                'error' => 'Usuário não autenticado ou conta não vinculada'
            ]);
        }

        $nickname = $user->minecraft_id;

        try {
            // Remove o nickname do usuário
            User::where('id', $user->id)->update([
                'minecraft_id' => null
            ]);

            // Envia mensagem de confirmação no Minecraft
            $command = "tellraw {$nickname} {\"text\":\"Sua conta foi desvinculada com sucesso!\",\"color\":\"green\"}";
            Artisan::call('minecraft:send-json-api-command', [
                'jsonCommand' => $command
            ]);

            return back()->with('status', 'Conta desvinculada com sucesso');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Erro ao desvincular conta: ' . $e->getMessage()
            ]);
        }
    }
}
