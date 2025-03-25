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
            return response()->json(['status' => 'ok']);
        }

        Log::info('[Kiwify Webhook] Início do processamento');

        // 1. Obter e validar payload JSON
        $rawPayload = $request->getContent();
        Log::debug('[Kiwify Webhook] Conteúdo bruto recebido', ['size' => strlen($rawPayload) . ' bytes']);

        // Remover comentários e limpar o payload
        $cleanedPayload = $this->cleanJsonPayload($rawPayload);
        $payload = json_decode($cleanedPayload, true);

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

        if (!$this->verifySignature($rawPayload, $signature, $secretKey)) {
            Log::warning('[Kiwify Webhook] Assinatura inválida', [
                'signature_recebida' => $signature,
                'payload_size' => strlen($rawPayload) . ' bytes'
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 3. Processar eventos
        Log::info('[Kiwify Webhook] Payload válido recebido', [
            'event_type' => $payload['webhook_event_type'] ?? 'unknown',
            'order_id' => $payload['order_id'] ?? 'unknown'
        ]);

        try {
            $this->processarEvento($payload);
        } catch (\Exception $e) {
            Log::error('[Kiwify Webhook] Erro no processamento', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Processing error'], 500);
        }

        Log::info('[Kiwify Webhook] Processamento concluído com sucesso');
        return response()->json(['status' => 'ok']);
    }

    private function cleanJsonPayload(string $payload): string
    {
        // Remover comentários de linha única
        $payload = preg_replace('!/\*.*?\*/!s', '', $payload);
        $payload = preg_replace('!//.*?\n!', '', $payload);
        
        // Remover espaços em excesso e quebras de linha
        $payload = preg_replace('/\s+/', ' ', $payload);
        
        return trim($payload);
    }

    private function verifySignature(string $rawPayload, ?string $signature, string $secretKey): bool
    {
        if (empty($signature)) {
            Log::debug('[Kiwify Webhook] Assinatura ausente na requisição');
            return false;
        }

        $calculatedSignature = hash_hmac('sha1', $rawPayload, $secretKey);
        return hash_equals($calculatedSignature, $signature);
    }

    private function processarEvento(array $dados)
    {
        $eventType = $dados['webhook_event_type'] ?? 'unknown';
        $logContext = [
            'event_type' => $eventType,
            'order_id' => $dados['order_id'] ?? 'unknown',
            'product_type' => $dados['product_type'] ?? 'unknown'
        ];

        Log::info("[Kiwify Webhook] Processando evento: $eventType", $logContext);

        switch ($eventType) {
            case 'order_approved':
                $this->handleOrderApproved($dados);
                break;
            case 'subscription_created':
                $this->handleSubscriptionCreated($dados);
                break;
            case 'subscription_cancelled':
                $this->handleSubscriptionCancelled($dados);
                break;
            default:
                Log::warning("[Kiwify Webhook] Evento não implementado: $eventType", $logContext);
        }
    }

    private function handleOrderApproved(array $dados)
    {
        Log::info('[Kiwify Webhook] Processando pedido aprovado', [
            'order_id' => $dados['order_id'] ?? 'unknown',
            'customer_email' => $dados['Customer']['email'] ?? 'unknown',
            'amount' => $dados['Commissions']['charge_amount'] ?? 'unknown',
            'product_type' => $dados['product_type'] ?? 'unknown'
        ]);
        
        // Implementar lógica para pedidos aprovados
    }

    private function handleSubscriptionCreated(array $dados)
    {
        Log::info('[Kiwify Webhook] Processando assinatura criada', [
            'subscription_id' => $dados['subscription_id'] ?? 'unknown',
            'plan_name' => $dados['Subscription']['plan']['name'] ?? 'unknown',
            'next_payment' => $dados['Subscription']['next_payment'] ?? 'unknown',
            'frequency' => $dados['Subscription']['plan']['frequency'] ?? 'unknown'
        ]);
        
        // Implementar lógica para novas assinaturas
    }

    private function handleSubscriptionCancelled(array $dados)
    {
        Log::info('[Kiwify Webhook] Processando assinatura cancelada', [
            'subscription_id' => $dados['subscription_id'] ?? 'unknown',
            'cancelation_reason' => $dados['Subscription']['cancelation_reason'] ?? 'unknown'
        ]);
        
        // Implementar lógica para assinaturas canceladas
    }
}
