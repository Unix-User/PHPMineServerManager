<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KiwifyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verificar se é uma requisição HEAD
        if ($request->isMethod('head')) {
            Log::info('[Kiwify Webhook] Requisição HEAD recebida');
            return response()->json(['status' => 'ok'], 200);
        }

        Log::info('[Kiwify Webhook] Início do processamento');

        // 1. Obter conteúdo bruto
        $rawPayload = $request->getContent();
        Log::info('[Kiwify Webhook] Conteúdo bruto recebido', ['raw' => $rawPayload]);

        // 2. Decodificar JSON
        $payload = json_decode($rawPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::info('[Kiwify Webhook] Payload JSON inválido', ['raw' => $rawPayload]);
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // 3. Verificar assinatura
        $signature = $request->query('signature');
        $secretKey = env('KIWIFY_WEBHOOK_SECRET');

        if (empty($secretKey)) {
            Log::info('[Kiwify Webhook] Chave secreta não configurada');
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        $calculatedSignature = hash_hmac('sha1', json_encode($payload), $secretKey);
        Log::info('[Kiwify Webhook] Verificação de assinatura', [
            'assinatura_recebida' => $signature,
            'assinatura_calculada' => $calculatedSignature
        ]);

        if ($signature !== $calculatedSignature) {
            Log::info('[Kiwify Webhook] Assinatura inválida', [
                'payload' => $payload,
                'signature_recebida' => $signature
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 4. Processar eventos
        Log::info('[Kiwify Webhook] Payload válido recebido', [
            'evento' => $payload['webhook_event_type'] ?? 'nenhum',
            'payload_size' => strlen($rawPayload) . ' bytes'
        ]);

        try {
            if (isset($payload['webhook_event_type'])) {
                $this->processarEvento($payload['webhook_event_type'], $payload);
            } else {
                Log::info('[Kiwify Webhook] Evento não especificado', $payload);
            }
        } catch (\Exception $e) {
            Log::info('[Kiwify Webhook] Erro no processamento', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }

        Log::info('[Kiwify Webhook] Processamento concluído com sucesso');
        return response()->json(['status' => 'ok'], 200);
    }

    private function processarEvento(string $evento, array $dados)
    {
        $logContext = ['evento' => $evento, 'dados' => $dados];
        Log::info("[Kiwify Webhook] Iniciando processamento para evento: $evento", $logContext);

        switch ($evento) {
            case 'order_approved':
                $this->handleOrderApproved($dados);
                break;
            default:
                Log::info("[Kiwify Webhook] Evento não implementado: $evento", $logContext);
        }

        Log::info("[Kiwify Webhook] Processamento concluído para evento: $evento");
    }

    private function handleOrderApproved(array $dados)
    {
        Log::info('[Kiwify Webhook] Processando pedido aprovado', [
            'order_id' => $dados['order_id'] ?? 'desconhecido',
            'customer' => $dados['Customer'] ?? []
        ]);
        
        // Implementar lógica para pedidos aprovados
    }
}
