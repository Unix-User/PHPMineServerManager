<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KiwifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Obter os dados do payload do webhook
        $payload = $request->all(); // ou $request->json()->all() se a Kiwify enviar JSON

        // 2. [Importante] Verificar a assinatura do webhook (segurança)
        $signature = $request->header('x-signature'); // ou o header correto da Kiwify
        $secret = env('KIWIFY_WEBHOOK_SECRET'); // Seu segredo webhook da Kiwify

        if (!$this->verifySignature($payload, $signature, $secret)) {
            Log::warning('Webhook da Kiwify: Assinatura inválida.');
            return response('Assinatura inválida', 403); // Retornar erro 403 se a assinatura for inválida
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

        // 4. Responder com um status 200 OK para indicar que o webhook foi recebido e processado
        return response('Webhook recebido', 200);
    }

    /**
     * Verifica a assinatura do webhook da Kiwify.
     * Consulte a documentação da Kiwify para o método correto de verificação.
     *
     * @param array $payload
     * @param string|null $signatureHeader
     * @param string $secret
     * @return bool
     */
    private function verifySignature(array $payload, ?string $signatureHeader, string $secret): bool
    {
        if (!$signatureHeader) {
            return false; // Sem header de assinatura, inválido
        }

        // Conforme a documentação da Kiwify (e prática comum), usa-se HMAC-SHA256
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $secret); // Assumindo payload como JSON

        // Comparar as assinaturas
        return hash_equals($signatureHeader, $expectedSignature);
    }
}
