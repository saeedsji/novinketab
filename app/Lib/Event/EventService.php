<?php

namespace App\Lib\Event;

use App\Lib\Journey\JourneyService;
use App\Models\Contact;
use App\Models\Event;
use App\Models\EventScore;
use App\Models\JourneyTrigger;
use Exception;
use Illuminate\Support\Facades\Auth;


class EventService
{
    protected int $userId;

    /**
     * @param int|null $userId The ID of the currently authenticated user (tenant).
     * If not provided, it's resolved from the Auth facade.
     */
    public function __construct(int $userId = null)
    {
        $this->userId = $userId ?? Auth::id();
    }

    /**
     * Creates a new event and triggers related actions like journeys and scoring.
     *
     * @param array $data Data for the new event, must include 'contact_id' and 'name'.
     * @return Event
     * @throws Exception
     */
    public function createEvent(array $data): Event
    {
        if (empty($data['contact_id'])) {
            throw new Exception("The contact identifier is required.", 400);
        }

        // Find the contact by UUID or external_id, ensuring it belongs to the current user.
        $contact = $this->findUserContactByIdentifier($data['contact_id']);

        // Prepare the data for creating the event.
        $eventData = [
            'user_id' => $this->userId,
            'contact_id' => $contact->id,
            'name' => $data['name'],
            'meta' => $data['meta'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ];

        // Create the event.
        $event = Event::create($eventData);

        // Handle post-creation tasks like journeys and scoring.
        $this->handlePostCreationTasks($event, $contact);

        return $event;
    }

    /**
     * Retrieves an event after verifying ownership.
     *
     * @param Event $event
     * @return Event
     * @throws Exception
     */
    public function showEvent(Event $event): Event
    {
        $this->ensureUserCanAccess($event);
        return $event;
    }

    /**
     * Updates an existing event after verifying ownership.
     *
     * @param Event $event
     * @param array $data The new data for the event.
     * @return Event
     * @throws Exception
     */
    public function updateEvent(Event $event, array $data): Event
    {
        $this->ensureUserCanAccess($event);

        // Merge meta data to prevent accidental deletion.
        if (isset($data['meta']) && is_array($data['meta'])) {
            $data['meta'] = array_merge($event->meta ?? [], $data['meta']);
        }

        $event->update($data);

        return $event->fresh();
    }

    /**
     * Deletes an event after verifying ownership.
     *
     * @param Event $event
     * @return void
     * @throws Exception
     */
    public function deleteEvent(Event $event): void
    {
        $this->ensureUserCanAccess($event);
        $event->delete();
    }

    /**
     * Finds a contact for the current user by its UUID or external_id.
     *
     * @param string $identifier
     * @return Contact
     * @throws Exception
     */
    private function findUserContactByIdentifier(string $identifier): Contact
    {
        $contact = Contact::where('user_id', $this->userId)
            ->where(function ($query) use ($identifier) {
                $query->where('uuid', $identifier)
                    ->orWhere('external_id', $identifier);
            })
            ->first();

        if (!$contact) {
            throw new Exception("Contact not found or you do not have permission to access it.", 404);
        }

        return $contact;
    }

    /**
     * Ensures the authenticated user has permission to access the given event.
     *
     * @param Event $event
     * @return void
     * @throws Exception
     */
    private function ensureUserCanAccess(Event $event): void
    {
        if ($event->user_id !== $this->userId) {
            throw new Exception("You do not have permission to access this event.", 403);
        }
    }

    /**
     * Handles post-creation tasks like triggering journeys and updating scores.
     *
     * @param Event $event The newly created event.
     * @param Contact $contact The contact associated with the event.
     */
    private function handlePostCreationTasks(Event $event, Contact $contact): void
    {
        // Section 1: Check and activate Journey Triggers
        $triggers = JourneyTrigger::where('event_name', $event->name)
            ->whereHas('journey', function ($query) {
                $query->where('user_id', $this->userId)->where('active', true);
            })
            ->get();

        foreach ($triggers as $trigger) {
            // Assuming JourneyService is available via the service container
            app(JourneyService::class)->startJourneyForContact($trigger->journey, $contact);
        }

        // Section 2: Calculate and add score
        $scoreRecord = EventScore::where('user_id', $this->userId)
            ->where('name', $event->name)
            ->first();

        if ($scoreRecord && $scoreRecord->score != 0) {
            $contact->increment('score', $scoreRecord->score);
        }
    }
}
