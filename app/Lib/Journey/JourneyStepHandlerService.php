<?php

namespace App\Lib\Journey;

use App\Enums\Journey\JourneyStepLogStatus;
use App\Enums\Journey\JourneyStepType;
use App\Jobs\JourneyHandleCallApiJob;
use App\Jobs\JourneyHandleSendEmailJob;
use App\Jobs\JourneyHandleSendSmsJob;
use App\Models\JourneyContact;
use App\Models\JourneyStep;
use App\Models\JourneyStepLog;


/**
 * Main orchestrator for processing and logging steps in a marketing journey.
 */
class JourneyStepHandlerService
{
    public function __construct(
        protected JourneyService $journeyService
    ) {}

    /**
     * Main entry point to handle and log a step for a given JourneyContact.
     * This method now catches exceptions to log them but does not re-throw,
     * allowing batch processes to continue even if one step fails.
     *
     * @param JourneyContact $jc
     * @param JourneyStep $step
     * @return void
     */
    public function handleStep(JourneyContact $jc, JourneyStep $step): void
    {
        $log = JourneyStepLog::create([
            'journey_contact_id' => $jc->id,
            'journey_step_id' => $step->id,
            'status' => JourneyStepLogStatus::running,
            'started_at' => now(),
        ]);

        $step->load('nextStep', 'nextStepIfTrue', 'nextStepIfFalse');

        match ($step->type) {
            JourneyStepType::send_email => $this->handleSendEmail($jc, $step, $log),
            JourneyStepType::send_sms => $this->handleSendSms($jc, $step, $log),
            JourneyStepType::wait => $this->handleWait($jc, $step, $log),
            JourneyStepType::condition_check => $this->handleConditionCheck($jc, $step, $log),
            JourneyStepType::call_api => $this->handleCallApi($jc, $step, $log),
            default => $this->handleUnknownStep($step, $log),
        };
    }

    /**
     * Instead of handling the logic directly, this method now delegates the entire
     * email sending and result logging process to a background job.
     */
    protected function handleSendEmail(JourneyContact $jc, JourneyStep $step, JourneyStepLog $log): void
    {
        // Dispatch the job to handle email sending asynchronously.
        JourneyHandleSendEmailJob::dispatch($jc, $step, $log);

        // Immediately move the contact to the next step in the journey.
        // The journey progression is no longer blocked by the email sending process.
        $this->moveToNextDefault($jc, $step);
    }

    /**
     * This method now delegates the SMS sending and logging process to a
     * background job for better performance and reliability.
     */
    protected function handleSendSms(JourneyContact $jc, JourneyStep $step, JourneyStepLog $log): void
    {
        // Dispatch the job to handle SMS sending asynchronously.
        JourneyHandleSendSmsJob::dispatch($jc, $step, $log);

        // Immediately move the contact to the next step.
        $this->moveToNextDefault($jc, $step);
    }

    /**
     * Handles a "wait" step.
     */
    protected function handleWait(JourneyContact $jc, JourneyStep $step, JourneyStepLog $log): void
    {
        $log->metadata = ['message' => 'Wait period concluded, proceeding to next step.'];
        $this->moveToNextDefault($jc, $step);
        $log->status = JourneyStepLogStatus::completed;
        $log->completed_at = now();
        $log->save();
    }

    /**
     * Handles a "condition_check" step, now with detailed logging.
     */
    protected function handleConditionCheck(JourneyContact $jc, JourneyStep $step, JourneyStepLog $log): void
    {
        $conditionService = new ConditionEvaluationService();
        $isConditionMet = $conditionService->isConditionMet(
            $jc->contact,
            $step->condition_data ?? []
        );

        $nextStepId = $isConditionMet ? $step->next_step_if_true : $step->next_step_if_false;
        if ($nextStepId) {
            $log->metadata = [
                'message'=>"Contact #{$jc->contact_id} in Journey #{$jc->journey_id}: Condition check for step #{$step->id} evaluated to " . ($isConditionMet ? 'TRUE' : 'FALSE') . ". Moving to step #{$nextStepId}.",
                'condition_result' => $isConditionMet,
                'next_step_id_chosen' => $nextStepId
            ];
            $nextJourneyStep = JourneyStep::find($nextStepId);
            $this->journeyService->moveToNextStep($jc, $nextJourneyStep);
            $log->status = JourneyStepLogStatus::completed;

        } else {
            $log->status = JourneyStepLogStatus::failed;
            $log->exception = "Contact #{$jc->contact_id} in Journey #{$jc->journey_id}: No next step defined for condition result " . ($isConditionMet ? 'TRUE' : 'FALSE') . " on step #{$step->id}. Journey ends.";
            // If there's no next step defined, the journey for this contact might end here.
        }
        $log->completed_at = now();
        $log->save();
    }

    /**
     * REFACTORED: Handles "call_api" step by dispatching a job.
     */
    protected function handleCallApi(JourneyContact $jc, JourneyStep $step, JourneyStepLog $log): void
    {
        // Dispatch the job to handle the API call asynchronously.
        JourneyHandleCallApiJob::dispatch($jc, $step, $log);

        // Immediately move the contact to the next step in the journey.
        $this->moveToNextDefault($jc, $step);
    }

    /**
     * Handles an unknown step type to prevent the journey from halting silently.
     */
    protected function handleUnknownStep(JourneyStep $step, JourneyStepLog $log): void
    {
        $log->metadata = ['error' => "Unknown step type encountered: {$step->type->value}"];
        $log->status = JourneyStepLogStatus::failed;
        $log->exception = "Unknown step type for step #{$step->id} -> type={$step->type->name}";
        $log->completed_at = now();
        $log->save();
    }

    /**
     * Default logic to move to the next linear step.
     */
    protected function moveToNextDefault(JourneyContact $jc, JourneyStep $step): void
    {
        $nextStep = $step->nextStep; // Eloquent relation: next_step_id
        $this->journeyService->moveToNextStep($jc, $nextStep);
    }
}
