<?php

namespace App\Lib\Sms;

/**
 * Ensures all SMS provider services have a consistent interface.
 */
interface SmsProviderInterface
{
    /**
     * Send an SMS message.
     *
     * @param string $receptor The recipient's phone number.
     * @param string $message The message content.
     * @param array $credentials The provider's credentials (e.g., api_key, username/password).
     * @param string|null $sender The sender number, if available.
     * @return array The result of the send operation.
     */
    public function send(string $receptor, string $message, array $credentials, ?string $sender): array;
}
