<?php

namespace App\Lib\Sms;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Handles sending SMS via Kavenegar.
 */
class KavenegarSmsService implements SmsProviderInterface
{
    public function send(string $receptor, string $message, array $credentials, ?string $sender): array
    {
        $apiKey = $credentials['api_key'] ?? null;

        if (!$apiKey || !$sender) {
            Log::error('Kavenegar: API key or sender number is missing.');
            return ['success' => false, 'message' => 'Provider configuration is incomplete.'];
        }

        try {
            $url = "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json";
            $response = Http::get($url, [
                'receptor' => $receptor,
                'sender' => $sender,
                'message' => urlencode($message),
            ]);

            $response->throw(); // Throw an exception for 4xx/5xx responses
            $responseData = $response->json();
            Log::info('Kavenegar SMS sent successfully.', ['response' => $responseData]);
            return ['success' => true, 'data' => $responseData];

        } catch (RequestException $e) {
            Log::critical('Kavenegar SMS sending failed.', [
                'error_message' => $e->getMessage(),
                'status_code' => $e->response ? $e->response->status() : 'N/A',
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
