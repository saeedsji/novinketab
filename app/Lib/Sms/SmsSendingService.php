<?php

namespace App\Lib\Sms;
use App\Enums\Sms\SmsProviderEnum;
use App\Models\SmsProvider;
use Illuminate\Support\Facades\Log;
/**
 * A factory service that dispatches SMS sending to the correct provider class.
 */
class SmsSendingService
{
    public function send(SmsProvider $provider, string $receptor, string $message): array
    {
        $service = $this->getProviderService($provider->provider);

        if (!$service) {
            Log::error("No SMS service configured for provider: {$provider->provider->label()}");
            return ['success' => false, 'message' => 'Provider service not found.'];
        }

        return $service->send(
            $receptor,
            $message,
            $provider->credentials, // Decrypted array from model
            $provider->sender
        );
    }

    protected function getProviderService(SmsProviderEnum $providerEnum): ?SmsProviderInterface
    {
        // This match statement acts as a factory for our provider services.
        return match ($providerEnum) {
            SmsProviderEnum::kavenegar   => app(KavenegarSmsService::class),
            // SmsProviderEnum::smsir       => app(SmsirSmsService::class),
            // SmsProviderEnum::melipayamak => app(MelipayamakSmsService::class),
            default                      => null,
        };
    }
}
