<?php

namespace App\Lib\Email;

use App\Mail\CustomMail;
use App\Models\EmailProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

/**
 * A reusable service to send an email using a dynamic provider configuration.
 */
class SendUsingProviderService
{
    /**
     * Send an email using a custom provider configuration.
     *
     * @param EmailProvider $provider The provider to use for sending.
     * @param string        $toEmail  The recipient's email address.
     * @param string        $toName   The recipient's name.
     * @param string        $subject  The email subject.
     * @param string        $body     The HTML content of the email.
     * @return void
     */
    public function handle(EmailProvider $provider, string $toEmail, string $toName, string $subject, string $body): void
    {
        try {
            // Build a custom mailer configuration array from the provider's settings.
            $customMailerConfig = [
                'transport'  => $provider->driver ?? 'smtp',
                'host'       => $provider->host,
                'port'       => $provider->port,
                'encryption' => $provider->encryption,
                'username'   => $provider->username,
                'password'   => $provider->password,
                'timeout'    => null,
                'auth_mode'  => null,
                'from' => [
                    'address' => $provider->from_address,
                    'name'    => $provider->from_name,
                ],
            ];

            // Set the dynamic configuration for a temporary 'custom' mailer.
            Config::set('mail.mailers.custom', $customMailerConfig);

            // Use the custom mailer to send the email with your existing mailable.
            Mail::mailer('custom')->to($toEmail, $toName)->send(new CustomMail($subject, $body));

        } catch (\Exception $e) {
            Log::critical("SendUsingProviderService failed.", [
                'provider_id'   => $provider->id,
                'recipient'     => $toEmail,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
