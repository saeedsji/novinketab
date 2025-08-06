<?php

namespace App\Lib\Gateway;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ZarinpalRequestClass
{
    protected string $apiUrl = 'https://api.zarinpal.com/pg/v4/payment/request.json';
    protected string $merchantId;

    public function __construct()
    {
        // Load the merchant ID from environment variables or configuration
        $this->merchantId = config('services.zarinpal.merchant_id');
    }

    /**
     * @throws Exception
     */
    public function execute($amount, $callbackUrl, $description, $mobile = null, $email = null)
    {
        $startpayUrl = "https://www.zarinpal.com/pg/StartPay/";
        $metadata = [];

        if (!empty($mobile)) {
            $metadata['mobile'] = $mobile;
        }
        if (!empty($email)) {
            $metadata['email'] = $email;
        }

        $payload = [
            'merchant_id' => $this->merchantId,
            'currency' => 'IRT',
            'amount' => $amount,
            'callback_url' => $callbackUrl,
            'description' => $description,
            'metadata' => $metadata,
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            $responseBody = json_decode($response->body(), true);

            if (isset($responseBody['data']['code']) && $responseBody['data']['code'] == 100) {
                return [
                    'code' => $responseBody['data']['code'],
                    'authority' => $responseBody['data']['authority'],
                    'url' => $startpayUrl . $responseBody['data']['authority'],
                    'fee' => $responseBody['data']['fee']
                ];
            }

            throw new Exception($responseBody['errors']['message'] ?? 'Unknown error', $responseBody['errors']['code'] ?? 0);
        } catch (Exception $e) {
            Log::error('Zarinpal Payment Request Error: ' . $e->getMessage());
            throw new Exception('Payment request failed: ' . $e->getMessage());
        }
    }
}
