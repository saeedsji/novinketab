<?php

namespace App\Lib\Journey;

use App\Models\Contact;
use App\Models\Journey;
use App\Models\JourneyContact;
use App\Models\JourneyStep;
use Illuminate\Support\Facades\Log;

class JourneyService
{
    /**
     * Start a journey for a contact if not already in progress.
     */
    public function startJourneyForContact(Journey $journey, Contact $contact): ?JourneyContact
    {
        // If journey is not active (boolean field), do nothing
        if (! $journey->active) {
            Log::info("Journey #{$journey->id} is inactive; ignoring start request for contact #{$contact->id}");
            return null;
        }

        // Check if contact already in this journey with status=in_progress
        $existing = JourneyContact::where('journey_id', $journey->id)
            ->where('contact_id', $contact->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return $existing; // Already in progress
        }

        // Find the first step (e.g., by lowest ID or some 'order' if you have it)
        $firstStep = $journey->steps()->orderBy('id')->first();
        if (! $firstStep) {
            Log::warning("Journey #{$journey->id} has no steps; cannot start for contact #{$contact->id}.");
            return null;
        }

        // Create a new journey_contact
        $jc = new JourneyContact();
        $jc->journey_id      = $journey->id;
        $jc->contact_id      = $contact->id;
        $jc->current_step_id = $firstStep->id;
        $jc->status          = 'in_progress';
        $jc->due_at          = now(); // ready to execute immediately
        $jc->save();

        return $jc;
    }

    /**
     * Move a contact from the current step to the next step.
     */
    public function moveToNextStep(JourneyContact $jc, ?JourneyStep $nextStep): void
    {
        if (! $nextStep) {
            // No next step => journey is completed
            $jc->current_step_id = null;
            $jc->status = 'completed';
            $jc->due_at = null;
            $jc->save();
            return;
        }

        $jc->current_step_id = $nextStep->id;

        // If there's a delay_time on the next step, schedule for the future
        if ($nextStep->delay_time) {
            $jc->due_at = now()->addMinutes($nextStep->delay_time);
        } else {
            $jc->due_at = now();
        }

        $jc->save();
    }

    /**
     * Force-complete a journey.
     */
    public function completeJourney(JourneyContact $jc): void
    {
        $jc->status          = 'completed';
        $jc->current_step_id = null;
        $jc->due_at          = null;
        $jc->save();
    }

    /**
     * Pause a journey for a contact.
     */
    public function pauseJourney(JourneyContact $jc): void
    {
        $jc->status = 'paused';
        $jc->save();
    }
}
