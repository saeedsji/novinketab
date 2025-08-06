<?php

namespace App\Lib\Journey;

use App\Models\Contact;
class ConditionEvaluationService
{
    /**
     * Evaluate all condition groups for a contact.
     * The main groups are connected by OR logic.
     *
     * @param Contact $contact
     * @param array|null $conditionGroups
     * @return bool
     */
    public function isConditionMet(Contact $contact, ?array $conditionGroups): bool
    {
        if (empty($conditionGroups)) {
            return true; // No conditions, so proceed.
        }

        // Any group being true (OR logic) makes the whole condition true.
        foreach ($conditionGroups as $group) {
            if ($this->evaluateRuleGroup($contact, $group)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate a single group of rules based on its logic (AND/OR).
     *
     * @param Contact $contact
     * @param array $group
     * @return bool
     */
    protected function evaluateRuleGroup(Contact $contact, array $group): bool
    {
        $rules = $group['rules'] ?? [];
        $logic = strtolower($group['logic'] ?? 'and');

        if (empty($rules)) {
            return true; // An empty group is considered true.
        }

        foreach ($rules as $rule) {
            $ruleResult = $this->checkRule($contact, $rule);

            if ($logic === 'and' && !$ruleResult) {
                return false; // In an AND group, one false rule makes the group false.
            }

            if ($logic === 'or' && $ruleResult) {
                return true; // In an OR group, one true rule makes the group true.
            }
        }

        // For an AND group, this means all rules were true.
        // For an OR group, this means no rules were true.
        return $logic === 'and';
    }

    /**
     * REFACTORED: Check a single rule against the contact's events using the 'meta' JSON field.
     *
     * @param Contact $contact
     * @param array $rule
     * @return bool
     */
    protected function checkRule(Contact $contact, array $rule): bool
    {
        $eventName = $rule['event'] ?? null;
        if (!$eventName) {
            return false;
        }

        $operator = $rule['operator'] ?? 'exists';

        // Base query for events with the specified name for the contact.
        $eventQuery = $contact->events()->where('name', $eventName);

        // If the operator is 'exists', we just need to know if any such event was recorded.
        if ($operator === 'exists') {
            return $eventQuery->exists();
        }

        // For all other operators, we need to check attributes within the 'meta' field.
        $attribute = $rule['attribute'] ?? null;
        $value = $rule['value'] ?? null;

        if ($attribute === null || $value === null) {
            return false; // Cannot perform comparison without attribute and value.
        }

        // Now, we apply the condition directly to the 'meta' column using your scope.
        // The previous 'whereHas' on a separate attributes table is no longer needed.
        if ($operator === 'contains') {
            // Your 'whereJsonMeta' scope handles LIKE if you pass it as the operator.
            $eventQuery->whereJsonMeta($attribute, 'LIKE', '%' . $value . '%');
        } else {
            // Your scope also handles standard operators like =, !=, >, <, etc.
            $eventQuery->whereJsonMeta($attribute, $operator, $value);
        }

        // Check if any event exists that matches the name AND the meta condition.
        return $eventQuery->exists();
    }
}
