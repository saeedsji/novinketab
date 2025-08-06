<?php

namespace App\Lib\Gateway;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalVerifyClass
{
    protected string $apiUrl = 'https://api.zarinpal.com/pg/v4/payment/verify.json';
    protected string $merchantId;

    public function __construct()
    {
        // Load the merchant ID from environment variables or configuration
        $this->merchantId = config('services.zarinpal.merchant_id');
    }


    public function attemp($amount, $authority)
    {
        $payload = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority,
        ];
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            $responseBody = json_decode($response->body(), true);

            // Ensure 'data' key exists and is an array
            if (isset($responseBody['data']) && is_array($responseBody['data'])) {
                $code = $responseBody['data']['code'] ?? null; // Use null as default if 'code' is not set

                if ($code === 100 || $code === 101) {
                    return [
                        'success' => true,
                        'code' => $code,
                        'message' => $responseBody['data']['message'] ?? 'No message provided',
                        'ref_id' => $responseBody['data']['ref_id'] ?? null,
                        'card_pan' => $responseBody['data']['card_pan'] ?? null,
                    ];
                } else {
                    return [
                        'success' => false,
                        'errors' => $responseBody['errors'] ?? ['Unknown error occurred'],
                    ];
                }
            } else {
                Log::error('Zarinpal Payment Verify Error: Invalid response structure', ['response' => $responseBody]);
                return [
                    'success' => false,
                    'message' => 'Invalid response structure',
                    'response' => $responseBody
                ];
            }
        } catch (\Exception $e) {
            Log::error('Zarinpal Payment Verify Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
