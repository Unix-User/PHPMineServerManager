<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KiwifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Obter os dados do payload do webhook
        // Kiwify envia o payload no corpo da requisição como JSON
        $payload = $request->json()->all();

        // 2. [Importante] Verificar a assinatura do webhook (segurança)
        // A assinatura agora vem como parâmetro GET 'signature'
        $signature = $request->query('signature');
        $secretKey = env('KIWIFY_WEBHOOK_SECRET'); // Seu segredo webhook da Kiwify

        if (!$this->verifySignature($payload, $signature, $secretKey)) {
            Log::warning('Webhook da Kiwify: Assinatura inválida.');
            return response()->json(['error' => 'Incorrect signature'], 400); // Retornar erro 400 com JSON
        }

        // 3. Processar o payload do webhook
        Log::info('Webhook da Kiwify recebido:', $payload);

        // Adicione aqui a lógica para processar os eventos da Kiwify
        // Exemplo:
        if (isset($payload['event'])) {
            if ($payload['event'] === 'order.paid') {
                // Lógica para pedido pago
                Log::info('Evento: Pedido Pago', $payload);
                // ... sua lógica aqui ...
            } elseif ($payload['event'] === 'product.updated') {
                // Lógica para produto atualizado
                Log::info('Evento: Produto Atualizado', $payload);
                // ... sua lógica aqui ...
            }
            // ... adicione outros eventos que você precisa tratar ...
        }

        // 4. Responder com um status 200 OK e JSON {'status' => 'ok'}
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Verifica a assinatura do webhook da Kiwify usando HMAC-SHA1.
     * Adaptação do código PHP fornecido.
     *
     * @param array $payload
     * @param string|null $signatureQueryString
     * @param string $secretKey
     * @return bool
     */
    private function verifySignature(array $payload, ?string $signatureQueryString, string $secretKey): bool
    {
        if (!$signatureQueryString) {
            return false; // Sem assinatura na query string, inválido
        }

        // Usar HMAC-SHA1 conforme o exemplo de código PHP
        $calculatedSignature = hash_hmac('sha1', json_encode($payload), $secretKey);

        // Comparar as assinaturas
        return hash_equals($signatureQueryString, $calculatedSignature);
    }
}
