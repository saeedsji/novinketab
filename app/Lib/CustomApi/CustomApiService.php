<?php

namespace App\Lib\CustomApi;

use App\Enums\CustomApi\CustomApiTypeEnum;
use App\Models\Contact;
use App\Models\CustomApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class CustomApiService
{
    /**
     * Calls a custom API with contact-specific data.
     *
     * @param CustomApi $api The API configuration to use.
     * @param Contact $contact The contact for whom the API is being called.
     * @return array An array containing 'success' (boolean) and 'data' (response body or error message).
     */
    public function callApi(CustomApi $api, Contact $contact): array
    {
        if (!$api->active) {
            return ['success' => false, 'data' => 'API is not active.'];
        }

        // Prepare contact data to be sent
        $contactData = [
            'uuid' => $contact->uuid,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'name' => $contact->name,
            'score' => $contact->score,
        ];

        // Merge contact data with the predefined body. Contact data takes precedence.
        $body = array_merge($api->body ?? [], $contactData);
        $headers = $api->headers ?? [];
        $endpoint = $this->replacePlaceholders($api->endpoint, $contact);

        try {
            $response = $this->sendRequest($api->method, $endpoint, $headers, $body);
            $response->throw(); // Throw an exception for 4xx/5xx responses

            $responseData = $response->json();

            // Special handling for coupon generation
            if ($api->type === CustomApiTypeEnum::GenerateCoupon) {
                if (!isset($responseData['coupon'])) {
                    return ['success' => false, 'data' => ['error' => "API response did not contain a 'coupon' field."]];
                }
            }

            return ['success' => true, 'data' => $responseData];

        } catch (\Exception $e) {
            return ['success' => false, 'data' => ['error' => $e->getMessage()]];
        }
    }

    /**
     * Sends the HTTP request based on the method.
     */
    private function sendRequest(string $method, string $endpoint, array $headers, array $body): Response
    {
        $pendingRequest = Http::withHeaders($headers);

        return match (strtoupper($method)) {
            'POST' => $pendingRequest->post($endpoint, $body),
            'PUT' => $pendingRequest->put($endpoint, $body),
            'GET' => $pendingRequest->get($endpoint, $body), // Body is sent as query params for GET
            'DELETE' => $pendingRequest->delete($endpoint, $body),
            default => throw new \Exception("Unsupported HTTP method: {$method}"),
        };
    }

    /**
     * Replaces placeholders like {contact_uuid} in the endpoint URL.
     */
    private function replacePlaceholders(string $endpoint, Contact $contact): string
    {
        return str_replace('{contact_uuid}', $contact->uuid, $endpoint);
    }
}

