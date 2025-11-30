<?php

namespace App\Services;

class PaymentService
{
    public function charge(array $payload): array
    {
        // Simulate charging and persist a payment record
        $transactionId = 'sim_txn_'.uniqid();
        $amount = isset($payload['amount']) ? floatval($payload['amount']) : 0;
        $method = $payload['payment_method'] ?? ($payload['method'] ?? 'simulated');
        // Basic simulation rules per method
        if (strpos($method, 'card') !== false) {
            // require card fields
            if (empty($payload['card_number']) || empty($payload['card_holder']) || empty($payload['expiry']) || empty($payload['cvc'])) {
                return ['success' => false, 'message' => 'Datos de tarjeta incompletos'];
            }

            // simple rudimentary validation: card number length
            $digits = preg_replace('/[^0-9]/', '', $payload['card_number']);
            if (strlen($digits) < 12) {
                return ['success' => false, 'message' => 'Número de tarjeta inválido'];
            }
        }

        if ($method === 'yape') {
            if (empty($payload['phone'])) {
                return ['success' => false, 'message' => 'Número de teléfono requerido para Yape'];
            }
            // simulate that certain phone numbers fail
            if (preg_match('/^9{2}/', $payload['phone'])) {
                // simulate failure for numbers starting with 99
                return ['success' => false, 'message' => 'Pago Yape rechazado por emisor'];
            }
        }

        // persist in payments table
        try {
            $id = \Illuminate\Support\Facades\DB::table('payments')->insertGetId([
                'order_id' => $payload['order_id'] ?? null,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'method' => $method,
                'status' => 'paid',
                'metadata' => json_encode($payload['metadata'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_id' => $id,
                'method' => $method,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
