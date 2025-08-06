<?php

namespace App\Lib\Contact;


use App\Models\Contact;
use Exception;
use Illuminate\Support\Facades\Auth;

class ContactService
{
    protected int $userId;

    /**
     * Instantiate the service, automatically setting the user ID from the authenticated user.
     */
    public function __construct()
    {
        $this->userId = Auth::id();
    }

    /**
     * Find a contact by its UUID or external_id for the authenticated user.
     *
     * @param string $identifier The UUID or external_id of the contact.
     * @return Contact
     * @throws Exception If the contact is not found or does not belong to the user.
     */
    public function findContactByIdentifier(string $identifier): Contact
    {
        $contact = Contact::where('user_id', $this->userId)
            ->where(function ($query) use ($identifier) {
                $query->where('uuid', $identifier)
                    ->orWhere('external_id', $identifier);
            })
            ->first();

        if (!$contact) {
            throw new Exception("User Not Found.");
        }

        return $contact;
    }


    /**
     * Creates a new contact.
     *
     * @param array $data Data for the new contact.
     * @return Contact
     * @throws Exception If a duplicate entry is found.
     */
    public function createContact(array $data): Contact
    {
        if (!empty($data['email']) && $this->isDuplicate('email', $data['email'])) {
            throw new Exception("A contact with this email already exists for you.");
        }

        if (!empty($data['phone']) && $this->isDuplicate('phone', $data['phone'])) {
            throw new Exception("A contact with this phone number already exists for you.");
        }

        if (!empty($data['external_id']) && $this->isDuplicate('external_id', $data['external_id'])) {
            throw new Exception("A contact with this external ID already exists for you.");
        }

        $data['user_id'] = $this->userId;
        return Contact::create($data);
    }

    /**
     * Updates an existing contact.
     *
     * @param Contact $contact The contact instance to update.
     * @param array $data The new data.
     * @return Contact
     * @throws Exception If there's a duplicate entry.
     */
    public function updateContact(Contact $contact, array $data): Contact
    {
        // Ownership is already checked by findContactByIdentifier, but as a safeguard:
        if ($contact->user_id !== $this->userId) {
            throw new Exception("You do not have permission to access this contact.");
        }

        if (!empty($data['email']) && $this->isDuplicate('email', $data['email'], $contact->id)) {
            throw new Exception("This email is already registered to another contact.");
        }

        if (!empty($data['phone']) && $this->isDuplicate('phone', $data['phone'], $contact->id)) {
            throw new Exception("This phone number is already registered to another contact.");
        }

        if (!empty($data['external_id']) && $this->isDuplicate('external_id', $data['external_id'], $contact->id)) {
            throw new Exception("This external ID is already registered to another contact.");
        }

        if (isset($data['meta']) && is_array($data['meta'])) {
            $data['meta'] = array_merge($contact->meta ?? [], $data['meta']);
        }

        $contact->update($data);
        return $contact->fresh();
    }

    /**
     * Deletes a contact.
     *
     * @param Contact $contact
     * @return void
     * @throws Exception If the contact does not belong to the user.
     */
    public function deleteContact(Contact $contact): void
    {
        // Ownership is already checked by findContactByIdentifier, but as a safeguard:
        if ($contact->user_id !== $this->userId) {
            throw new Exception("You do not have permission to access this contact.");
        }
        $contact->delete();
    }

    /**
     * Helper method to check for duplicate fields.
     *
     * @param string $field 'email', 'phone', or 'external_id'.
     * @param string $value The value to check.
     * @param int|null $exceptId The ID of the contact to exclude from the check (for updates).
     * @return bool
     */
    private function isDuplicate(string $field, string $value, ?int $exceptId = null): bool
    {
        $query = Contact::where('user_id', $this->userId)->where($field, $value);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }
}

