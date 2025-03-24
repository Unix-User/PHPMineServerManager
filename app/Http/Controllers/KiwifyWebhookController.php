<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KiwifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('[Kiwify Webhook] Início do processamento');

        // 1. Obter conteúdo bruto e validar JSON
        $rawPayload = $request->getContent();
        Log::debug('[Kiwify Webhook] Conteúdo bruto recebido', ['raw' => $rawPayload]);

        $payload = json_decode($rawPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('[Kiwify Webhook] Payload JSON inválido', ['raw' => $rawPayload]);
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // 2. Verificar assinatura
        $signature = $request->query('signature');
        $secretKey = env('KIWIFY_WEBHOOK_SECRET');

        if (empty($secretKey)) {
            Log::critical('[Kiwify Webhook] Chave secreta não configurada');
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        Log::debug('[Kiwify Webhook] Parâmetros de verificação', [
            'signature_recebida' => $signature,
            'secret_key' => substr($secretKey, 0, 4) . '...' // Log parcial por segurança
        ]);

        if (!$this->verifySignature($rawPayload, $signature, $secretKey)) {
            Log::warning('[Kiwify Webhook] Assinatura inválida', [
                'payload' => $rawPayload,
                'signature_recebida' => $signature
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 3. Processar eventos
        Log::info('[Kiwify Webhook] Payload válido recebido', [
            'evento' => $payload['event'] ?? 'nenhum',
            'payload_size' => strlen($rawPayload) . ' bytes'
        ]);

        try {
            if (isset($payload['event'])) {
                $this->processarEvento($payload['event'], $payload);
            } else {
                Log::warning('[Kiwify Webhook] Evento não especificado', $payload);
            }
        } catch (\Exception $e) {
            Log::error('[Kiwify Webhook] Erro no processamento', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }

        Log::info('[Kiwify Webhook] Processamento concluído com sucesso');
        return response()->json(['status' => 'success']);
    }

    private function verifySignature(string $rawPayload, ?string $signature, string $secretKey): bool
    {
        if (empty($signature)) {
            Log::debug('[Kiwify Webhook] Assinatura ausente na requisição');
            return false;
        }

        $calculatedSignature = hash_hmac('sha1', $rawPayload, $secretKey);
        Log::debug('[Kiwify Webhook] Assinatura calculada', ['assinatura' => $calculatedSignature]);

        return hash_equals($calculatedSignature, $signature);
    }

    private function processarEvento(string $evento, array $dados)
    {
        $logContext = ['evento' => $evento, 'dados' => $dados];
        Log::info("[Kiwify Webhook] Iniciando processamento para evento: $evento", $logContext);

        switch ($evento) {
            case 'order.paid':
                $this->handleOrderPaid($dados);
                break;
            case 'product.updated':
                $this->handleProductUpdated($dados);
                break;
            default:
                Log::warning("[Kiwify Webhook] Evento não implementado: $evento", $logContext);
        }

        Log::info("[Kiwify Webhook] Processamento concluído para evento: $evento");
    }

    private function handleOrderPaid(array $dados)
    {
        // Implementar lógica para pedidos pagos
        Log::info('[Kiwify Webhook] Processando pedido pago', ['order_id' => $dados['order_id'] ?? 'desconhecido']);
    }

    private function handleProductUpdated(array $dados)
    {
        // Implementar lógica para produtos atualizados
        Log::info('[Kiwify Webhook] Processando produto atualizado', ['product_id' => $dados['product_id'] ?? 'desconhecido']);
    }
}
