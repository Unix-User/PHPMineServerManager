<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KiwifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Logar o recebimento inicial do webhook
        Log::info('Início do processamento do webhook da Kiwify');

        // 1. Obter os dados do payload do webhook
        $payload = $request->json()->all();
        Log::debug('Payload recebido:', $payload);

        // 2. Verificar a assinatura do webhook
        $signature = $request->query('signature');
        $secretKey = env('KIWIFY_WEBHOOK_SECRET');
        Log::debug('Assinatura recebida:', ['signature' => $signature]);

        if (!$this->verifySignature($payload, $signature, $secretKey)) {
            Log::warning('Webhook da Kiwify: Assinatura inválida.', [
                'received_signature' => $signature,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Incorrect signature'], 400);
        }

        Log::info('Assinatura do webhook validada com sucesso');

        // 3. Processar o payload do webhook
        Log::info('Webhook da Kiwify recebido:', [
            'event' => $payload['event'] ?? 'unknown',
            'payload' => $payload
        ]);

        if (isset($payload['event'])) {
            Log::info('Evento detectado: ' . $payload['event']);
            
            if ($payload['event'] === 'order.paid') {
                Log::info('Processando evento: Pedido Pago', $payload);
                // ... sua lógica aqui ...
            } elseif ($payload['event'] === 'product.updated') {
                Log::info('Processando evento: Produto Atualizado', $payload);
                // ... sua lógica aqui ...
            }
        } else {
            Log::warning('Webhook recebido sem evento definido', $payload);
        }

        // 4. Responder com sucesso
        Log::info('Webhook processado com sucesso');
        return response()->json(['status' => 'ok'], 200);
    }

    private function verifySignature(array $payload, ?string $signatureQueryString, string $secretKey): bool
    {
        if (!$signatureQueryString) {
            Log::warning('Tentativa de verificação de assinatura sem signature');
            return false;
        }

        $calculatedSignature = hash_hmac('sha1', json_encode($payload), $secretKey);
        Log::debug('Assinatura calculada:', ['calculated_signature' => $calculatedSignature]);

        return hash_equals($signatureQueryString, $calculatedSignature);
    }
}
