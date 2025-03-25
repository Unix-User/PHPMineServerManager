<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;

class KiwifyWebhookController extends Controller
{
    private const LOG_PREFIX = '[Kiwify Webhook]';
    private const EVENT_HANDLERS = [
        'order_approved' => 'handleOrderApproved',
        'billet_created' => 'handleBilletCreated',
        'pix_created' => 'handlePixCreated',
        'order_rejected' => 'handleOrderRejected',
        'order_refunded' => 'handleOrderRefunded',
        'chargeback' => 'handleChargeback',
    ];

    public function handle(Request $request)
    {
        if ($request->isMethod('head')) {
            Log::info(self::LOG_PREFIX . ' HEAD request received');
            return response()->json(['status' => 'ok']);
        }

        Log::info(self::LOG_PREFIX . ' Processing started');

        try {
            $payload = $this->validateAndDecodePayload($request);
            $this->verifyRequestSignature($request, $payload['raw']);
            $this->processEvent($payload['decoded']);
            
            Log::info(self::LOG_PREFIX . ' Processing completed successfully');
            return response()->json(['status' => 'ok']);
            
        } catch (\Exception $e) {
            Log::error(self::LOG_PREFIX . ' Error: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->getContent()
            ]);
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    private function validateAndDecodePayload(Request $request): array
    {
        $rawPayload = $request->getContent();
        Log::debug(self::LOG_PREFIX . ' Raw payload received', ['size' => strlen($rawPayload) . ' bytes']);

        $cleanedPayload = $this->cleanJsonPayload($rawPayload);
        $decodedPayload = json_decode($cleanedPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON inválido', 400);
        }

        if (empty($decodedPayload['order_id'])) {
            throw new \Exception('ID do pedido ausente no payload', 400);
        }

        return [
            'raw' => $rawPayload,
            'decoded' => $decodedPayload
        ];
    }

    private function verifyRequestSignature(Request $request, string $rawPayload): void
    {
        $signature = $request->query('signature');
        $secretKey = env('KIWIFY_WEBHOOK_SECRET');

        if (empty($secretKey)) {
            Log::warning(self::LOG_PREFIX . ' Chave secreta do webhook Kiwify não configurada - Acesso permitido sem verificação de assinatura');
            return;
        }

        if (!$this->verifySignature($rawPayload, $signature, $secretKey)) {
            Log::warning(self::LOG_PREFIX . ' Assinatura inválida', [
                'received_signature' => $signature,
                'payload_size' => strlen($rawPayload) . ' bytes'
            ]);
            throw new \Exception('Assinatura inválida', 401);
        }
    }

    private function cleanJsonPayload(string $payload): string
    {
        return trim(preg_replace([
            '!/\*.*?\*/!s',
            '!//.*?\n!',
            '/\s+/'
        ], ['', '', ' '], $payload));
    }

    private function verifySignature(string $rawPayload, ?string $signature, string $secretKey): bool
    {
        if (empty($signature)) {
            Log::debug(self::LOG_PREFIX . ' Assinatura ausente na requisição');
            return false;
        }

        $calculatedSignature = hash_hmac('sha1', $rawPayload, $secretKey);
        return hash_equals($calculatedSignature, $signature);
    }

    private function processEvent(array $data): void
    {
        $eventType = $data['webhook_event_type'] ?? 'unknown';
        $logContext = [
            'event_type' => $eventType,
            'order_id' => $data['order_id'] ?? 'unknown',
            'product_type' => $data['product_type'] ?? 'unknown'
        ];

        Log::info(self::LOG_PREFIX . " Processando evento: {$eventType}", $logContext);

        if (isset(self::EVENT_HANDLERS[$eventType])) {
            try {
                $this->{self::EVENT_HANDLERS[$eventType]}($data);
            } catch (\Exception $e) {
                Log::error(self::LOG_PREFIX . " Erro ao processar evento {$eventType}", [
                    'order_id' => $data['order_id'] ?? 'unknown',
                    'exception' => $e,
                    'stacktrace' => $e->getTraceAsString() // Adicionado stacktrace para melhor depuração
                ]);
                throw new \Exception("Erro ao processar evento {$eventType}: " . $e->getMessage(), 500);
            }
        } else {
            Log::warning(self::LOG_PREFIX . " Evento não implementado: {$eventType}", $logContext);
        }
    }

    private function updatePurchaseStatus(array $data, string $status, string $logMessage): void
    {
        $purchaseData = [
            'order_id' => $data['order_id'],
            'status' => $status,
            'customer_email' => $data['Customer']['email'] ?? null,
            'product_type' => $data['product_type'] ?? null,
            'amount' => $data['Commissions']['charge_amount'] ?? null,
            'shop_item_id' => $data['Product']['product_id'] ?? null,
            'user_id' => $this->getUserIdFromCustomerData($data) // Adicionado mapeamento de user_id
        ];

        try {
            Purchase::updateOrCreate(
                ['order_id' => $data['order_id']],
                $purchaseData
            );
            Log::info(self::LOG_PREFIX . " Status do pedido atualizado para {$logMessage}", ['order_id' => $data['order_id']]);
        } catch (\Exception $e) {
            Log::error(self::LOG_PREFIX . ' Erro ao atualizar compra', [
                'order_id' => $data['order_id'],
                'exception' => $e,
                'purchase_data' => $purchaseData,
                'stacktrace' => $e->getTraceAsString() // Adicionado stacktrace para melhor depuração
            ]);
            throw new \Exception('Erro ao processar compra: ' . $e->getMessage(), 500);
        }
    }

    private function getUserIdFromCustomerData(array $data): ?int
    {
        // Implementar lógica para mapear o email do cliente para um user_id
        // Exemplo básico:
        $email = $data['Customer']['email'] ?? null;
        if ($email) {
            // Aqui você pode implementar a lógica para buscar o user_id
            // baseado no email do cliente
            return null; // Retornar null por enquanto
        }
        return null;
    }

    private function handleOrderApproved(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando pedido aprovado', [
            'order_id' => $data['order_id'] ?? 'unknown',
            'customer_email' => $data['Customer']['email'] ?? 'unknown',
            'amount' => $data['Commissions']['charge_amount'] ?? 'unknown',
            'product_type' => $data['product_type'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'approved', 'pedido aprovado');
    }

    private function handleBilletCreated(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando boleto criado', [
            'order_id' => $data['order_id'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'boleto_gerado', 'boleto gerado');
    }

    private function handlePixCreated(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando pix criado', [
            'order_id' => $data['order_id'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'pix_gerado', 'pix gerado');
    }

    private function handleOrderRejected(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando pedido rejeitado', [
            'order_id' => $data['order_id'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'rejected', 'compra recusada');
    }

    private function handleOrderRefunded(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando reembolso', [
            'order_id' => $data['order_id'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'refunded', 'reembolso');
    }

    private function handleChargeback(array $data): void
    {
        Log::info(self::LOG_PREFIX . ' Processando chargeback', [
            'order_id' => $data['order_id'] ?? 'unknown'
        ]);

        $this->updatePurchaseStatus($data, 'chargeback', 'chargeback');
    }
}
