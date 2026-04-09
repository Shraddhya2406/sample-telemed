<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StickyPaymentService
{
    /**
     * Process a payment through Sticky CRM.
     *
     * Expects an array with card and billing fields. The service will merge
     * credentials from config('services.sticky').
     *
     * Returns ['success' => bool, 'response' => array|string, 'message' => string|null]
     */
    public function processPayment(array $data): array
    {
        $url = config('services.sticky.url');
        $username = config('services.sticky.username');
        $password = config('services.sticky.password');
        $campaignId = config('services.sticky.campaignId');
        $productId = config('services.sticky.productId');

        if (! $url || ! $username || ! $password) {
            Log::error('StickyPaymentService: missing configuration');
            return ['success' => false, 'message' => 'Payment provider not configured', 'response' => null];
        }

        // Build payload - include credentials required by Sticky CRM.
        $payload = array_filter(array_merge([
            'loginId' => $username,
            'password' => $password,
            'campaignId' => $campaignId,
            'productId' => $productId,
        ], $data), function ($v) {
            // Do not send null values
            return $v !== null;
        });

        try {
            $response = Http::asForm()->post($url, $payload);
            // $response = Http::asForm()->post($url, [
            //     'loginId' => 'CCDEVUSER',
            //     'password' => 'HA9mzkfNkm7dm',
            // ]);
            // Try to parse JSON safely
            $body = null;
            try {
                $body = $response->json();
            } catch (\Throwable $e) {
                $body = $response->body();
            }

            dd($payload, $body);
            if (! $response->successful()) {
                Log::warning('StickyPaymentService: non-success HTTP response', ['status' => $response->status(), 'body' => is_array($body) ? $body : substr($body, 0, 200)]);
                return ['success' => false, 'message' => 'Payment provider error', 'response' => $body];
            }

            // Check business-level response code (API uses responseCode == 100 for success)
            $responseCode = null;
            if (is_array($body)) {
                $responseCode = $body['responseCode'] ?? $body['response_code'] ?? null;
            }

            if ($responseCode !== null) {
                if ((int) $responseCode === 100) {
                    return ['success' => true, 'response' => $body, 'message' => null];
                }

                $msg = is_array($body) ? ($body['message'] ?? $body['msg'] ?? 'Payment failed') : 'Payment failed';
                return ['success' => false, 'response' => $body, 'message' => $msg];
            }

            // Fallback: if no responseCode, treat any successful HTTP as success but pass body back
            return ['success' => true, 'response' => $body, 'message' => null];
        } catch (\Throwable $e) {
            // Do not log sensitive request payload (card details). Only log the exception message.
            Log::error('StickyPaymentService exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment request failed', 'response' => null];
        }
    }
}
