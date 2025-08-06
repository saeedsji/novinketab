<?php

namespace App\Lib\Sms;

use App\Models\JourneyContact;
use App\Models\JourneyStep;
use Illuminate\Support\Facades\Log;

/**
 * Handles all logic for a 'send_sms' journey step.
 */
class JourneySmsHandlerService
{
    protected SmsSendingService $smsService;

    public function __construct(SmsSendingService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(JourneyContact $jc, JourneyStep $step): void
    {
        $contact = $jc->contact;
        if (empty($contact->phone)) {
            Log::warning("Contact #{$contact->id} has no phone number. Skipping SMS on step #{$step->id}.");
            return;
        }

        // 1. Fetch template with its provider.
        $template = $step->smsTemplate()->with('smsProvider')->first();

        // 2. Perform validations.
        if (!$template) {
            Log::error("SMS template #{$step->sms_template_id} not found for step #{$step->id}.");
            return;
        }
        if (!$template->active) {
            Log::warning("SMS template '{$template->name}' is inactive. Skipping step #{$step->id}.");
            return;
        }

        $provider = $template->smsProvider;
        if (!$provider) {
            Log::error("SMS Provider not found for template #{$template->id} on step #{$step->id}.");
            return;
        }
        if (empty($provider->is_active)) { // Assuming is_active field exists
            Log::warning("SMS Provider '{$provider->provider->label()}' is inactive. Skipping step #{$step->id}.");
            return;
        }

        // 3. Delegate to the generic sending service.
        $this->smsService->send(
            $provider,
            $contact->phone,
            $template->content
        );

        Log::info("JourneySmsHandler has processed step #{$step->id} for contact #{$contact->id}.");
    }
}
