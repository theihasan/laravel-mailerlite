<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\DTOs;

use InvalidArgumentException;

/**
 * Automation Data Transfer Object
 *
 * This class represents automation data with validation and normalization.
 * It ensures that automation information is properly formatted and valid
 * before being sent to the MailerLite API.
 */
class AutomationDTO
{
    /**
     * Create a new automation DTO.
     *
     * @param  string  $name  Automation name (required)
     * @param  bool  $enabled  Whether the automation is enabled (default: true)
     * @param  array  $triggers  Automation triggers configuration (required)
     * @param  array  $steps  Automation steps/actions (required)
     * @param  string|null  $description  Automation description (optional)
     * @param  array  $settings  Additional automation settings (optional)
     * @param  array  $conditions  Automation conditions (optional)
     * @param  string  $status  Automation status: draft, active, paused, completed (default: draft)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $name,
        public readonly bool $enabled = true,
        public readonly array $triggers = [],
        public readonly array $steps = [],
        public readonly ?string $description = null,
        public readonly array $settings = [],
        public readonly array $conditions = [],
        public readonly string $status = 'draft',
    ) {
        $this->validateName($name);
        $this->validateTriggers($triggers);
        $this->validateSteps($steps);
        $this->validateStatus($status);
        $this->validateSettings($settings);
        $this->validateConditions($conditions);
    }

    /**
     * Create an automation DTO from an array.
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'] ?? throw new InvalidArgumentException('Name is required'),
            enabled: $data['enabled'] ?? true,
            triggers: $data['triggers'] ?? [],
            steps: $data['steps'] ?? [],
            description: $data['description'] ?? null,
            settings: $data['settings'] ?? [],
            conditions: $data['conditions'] ?? [],
            status: $data['status'] ?? 'draft',
        );
    }

    /**
     * Create a basic automation with name and enabled status.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $name, bool $enabled = true): static
    {
        return new static(name: $name, enabled: $enabled);
    }

    /**
     * Create an automation with triggers and steps.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithFlow(string $name, array $triggers, array $steps): static
    {
        return new static(
            name: $name,
            triggers: $triggers,
            steps: $steps
        );
    }

    /**
     * Create a subscriber-based automation.
     *
     * @throws InvalidArgumentException
     */
    public static function createSubscriberAutomation(string $name, string $triggerEvent, array $steps): static
    {
        $triggers = [
            [
                'type' => 'subscriber',
                'event' => $triggerEvent, // 'joins_group', 'subscribes', 'updates_field', etc.
            ],
        ];

        return new static(
            name: $name,
            triggers: $triggers,
            steps: $steps
        );
    }

    /**
     * Create a date-based automation.
     *
     * @throws InvalidArgumentException
     */
    public static function createDateAutomation(string $name, string $dateField, int $offsetDays, array $steps): static
    {
        $triggers = [
            [
                'type' => 'date',
                'field' => $dateField,
                'offset' => $offsetDays,
                'unit' => 'days',
            ],
        ];

        return new static(
            name: $name,
            triggers: $triggers,
            steps: $steps
        );
    }

    /**
     * Convert the DTO to an array for API submission.
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'enabled' => $this->enabled,
            'status' => $this->status,
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if (! empty($this->triggers)) {
            $data['triggers'] = $this->triggers;
        }

        if (! empty($this->steps)) {
            $data['steps'] = $this->steps;
        }

        if (! empty($this->settings)) {
            $data['settings'] = $this->settings;
        }

        if (! empty($this->conditions)) {
            $data['conditions'] = $this->conditions;
        }

        return $data;
    }

    /**
     * Get a copy of the DTO with updated fields.
     *
     * @throws InvalidArgumentException
     */
    public function with(array $updates): static
    {
        return static::fromArray(array_merge($this->toArray(), $updates));
    }

    /**
     * Get a copy with a different name.
     */
    public function withName(string $name): static
    {
        return new static(
            name: $name,
            enabled: $this->enabled,
            triggers: $this->triggers,
            steps: $this->steps,
            description: $this->description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $this->status,
        );
    }

    /**
     * Get a copy with different enabled status.
     */
    public function withEnabled(bool $enabled): static
    {
        return new static(
            name: $this->name,
            enabled: $enabled,
            triggers: $this->triggers,
            steps: $this->steps,
            description: $this->description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $this->status,
        );
    }

    /**
     * Get a copy with a description.
     */
    public function withDescription(string $description): static
    {
        return new static(
            name: $this->name,
            enabled: $this->enabled,
            triggers: $this->triggers,
            steps: $this->steps,
            description: $description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $this->status,
        );
    }

    /**
     * Get a copy with different triggers.
     */
    public function withTriggers(array $triggers): static
    {
        return new static(
            name: $this->name,
            enabled: $this->enabled,
            triggers: $triggers,
            steps: $this->steps,
            description: $this->description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $this->status,
        );
    }

    /**
     * Get a copy with different steps.
     */
    public function withSteps(array $steps): static
    {
        return new static(
            name: $this->name,
            enabled: $this->enabled,
            triggers: $this->triggers,
            steps: $steps,
            description: $this->description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $this->status,
        );
    }

    /**
     * Get a copy with different status.
     */
    public function withStatus(string $status): static
    {
        return new static(
            name: $this->name,
            enabled: $this->enabled,
            triggers: $this->triggers,
            steps: $this->steps,
            description: $this->description,
            settings: $this->settings,
            conditions: $this->conditions,
            status: $status,
        );
    }

    /**
     * Validate automation name.
     *
     * @throws InvalidArgumentException
     */
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Automation name cannot be empty.');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Automation name cannot exceed 255 characters.');
        }
    }

    /**
     * Validate automation triggers.
     *
     * @throws InvalidArgumentException
     */
    private function validateTriggers(array $triggers): void
    {
        if (empty($triggers)) {
            throw new InvalidArgumentException('Automation must have at least one trigger.');
        }

        foreach ($triggers as $index => $trigger) {
            if (! is_array($trigger)) {
                throw new InvalidArgumentException("Trigger at index {$index} must be an array.");
            }

            if (! isset($trigger['type'])) {
                throw new InvalidArgumentException("Trigger at index {$index} must have a 'type' field.");
            }

            $validTypes = ['subscriber', 'date', 'api', 'webhook', 'custom_field'];
            if (! in_array($trigger['type'], $validTypes, true)) {
                throw new InvalidArgumentException(
                    "Invalid trigger type '{$trigger['type']}' at index {$index}. Valid types: ".implode(', ', $validTypes)
                );
            }

            // Additional validation based on trigger type
            $this->validateTriggerByType($trigger, $index);
        }
    }

    /**
     * Validate trigger based on its type.
     *
     * @throws InvalidArgumentException
     */
    private function validateTriggerByType(array $trigger, int $index): void
    {
        switch ($trigger['type']) {
            case 'subscriber':
                if (! isset($trigger['event'])) {
                    throw new InvalidArgumentException("Subscriber trigger at index {$index} must have an 'event' field.");
                }
                $validEvents = ['joins_group', 'subscribes', 'unsubscribes', 'updates_field', 'completes_automation'];
                if (! in_array($trigger['event'], $validEvents, true)) {
                    throw new InvalidArgumentException(
                        "Invalid subscriber event '{$trigger['event']}' at index {$index}. Valid events: ".implode(', ', $validEvents)
                    );
                }
                break;

            case 'date':
                if (! isset($trigger['field'])) {
                    throw new InvalidArgumentException("Date trigger at index {$index} must have a 'field' field.");
                }
                if (! isset($trigger['offset'])) {
                    throw new InvalidArgumentException("Date trigger at index {$index} must have an 'offset' field.");
                }
                break;

            case 'api':
            case 'webhook':
                if (! isset($trigger['endpoint'])) {
                    throw new InvalidArgumentException("{$trigger['type']} trigger at index {$index} must have an 'endpoint' field.");
                }
                break;
        }
    }

    /**
     * Validate automation steps.
     *
     * @throws InvalidArgumentException
     */
    private function validateSteps(array $steps): void
    {
        if (empty($steps)) {
            throw new InvalidArgumentException('Automation must have at least one step.');
        }

        foreach ($steps as $index => $step) {
            if (! is_array($step)) {
                throw new InvalidArgumentException("Step at index {$index} must be an array.");
            }

            if (! isset($step['type'])) {
                throw new InvalidArgumentException("Step at index {$index} must have a 'type' field.");
            }

            $validTypes = ['email', 'delay', 'condition', 'action', 'webhook', 'tag', 'field_update'];
            if (! in_array($step['type'], $validTypes, true)) {
                throw new InvalidArgumentException(
                    "Invalid step type '{$step['type']}' at index {$index}. Valid types: ".implode(', ', $validTypes)
                );
            }

            // Additional validation based on step type
            $this->validateStepByType($step, $index);
        }
    }

    /**
     * Validate step based on its type.
     *
     * @throws InvalidArgumentException
     */
    private function validateStepByType(array $step, int $index): void
    {
        switch ($step['type']) {
            case 'email':
                if (! isset($step['campaign_id']) && ! isset($step['template_id'])) {
                    throw new InvalidArgumentException("Email step at index {$index} must have either 'campaign_id' or 'template_id'.");
                }
                break;

            case 'delay':
                if (! isset($step['duration']) || ! isset($step['unit'])) {
                    throw new InvalidArgumentException("Delay step at index {$index} must have 'duration' and 'unit' fields.");
                }
                $validUnits = ['minutes', 'hours', 'days', 'weeks'];
                if (! in_array($step['unit'], $validUnits, true)) {
                    throw new InvalidArgumentException(
                        "Invalid delay unit '{$step['unit']}' at index {$index}. Valid units: ".implode(', ', $validUnits)
                    );
                }
                break;

            case 'condition':
                if (! isset($step['conditions'])) {
                    throw new InvalidArgumentException("Condition step at index {$index} must have 'conditions' field.");
                }
                break;

            case 'webhook':
                if (! isset($step['url'])) {
                    throw new InvalidArgumentException("Webhook step at index {$index} must have 'url' field.");
                }
                if (! filter_var($step['url'], FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException("Invalid webhook URL at step index {$index}.");
                }
                break;
        }
    }

    /**
     * Validate automation status.
     *
     * @throws InvalidArgumentException
     */
    private function validateStatus(string $status): void
    {
        $validStatuses = ['draft', 'active', 'paused', 'completed', 'disabled'];

        if (! in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException(
                "Invalid status '{$status}'. Valid statuses: ".implode(', ', $validStatuses)
            );
        }
    }

    /**
     * Validate automation settings.
     *
     * @throws InvalidArgumentException
     */
    private function validateSettings(array $settings): void
    {
        // Validate common settings
        if (isset($settings['timezone']) && ! in_array($settings['timezone'], timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException("Invalid timezone '{$settings['timezone']}'.");
        }

        if (isset($settings['send_time']) && ! is_array($settings['send_time'])) {
            throw new InvalidArgumentException('Send time settings must be an array.');
        }

        if (isset($settings['frequency_cap']) && (! is_int($settings['frequency_cap']) || $settings['frequency_cap'] < 1)) {
            throw new InvalidArgumentException('Frequency cap must be a positive integer.');
        }
    }

    /**
     * Validate automation conditions.
     *
     * @throws InvalidArgumentException
     */
    private function validateConditions(array $conditions): void
    {
        foreach ($conditions as $index => $condition) {
            if (! is_array($condition)) {
                throw new InvalidArgumentException("Condition at index {$index} must be an array.");
            }

            if (! isset($condition['field']) || ! isset($condition['operator']) || ! isset($condition['value'])) {
                throw new InvalidArgumentException("Condition at index {$index} must have 'field', 'operator', and 'value' fields.");
            }

            $validOperators = ['equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than', 'exists', 'not_exists'];
            if (! in_array($condition['operator'], $validOperators, true)) {
                throw new InvalidArgumentException(
                    "Invalid condition operator '{$condition['operator']}' at index {$index}. Valid operators: ".implode(', ', $validOperators)
                );
            }
        }
    }
}
