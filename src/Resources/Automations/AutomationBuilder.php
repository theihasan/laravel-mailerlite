<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Automations;

use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;

/**
 * Automation Builder
 *
 * Provides a fluent, plain-English API for building automation operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::automations()
 *       ->create('Welcome Series')
 *       ->description('Welcome new subscribers')
 *       ->trigger('subscriber', 'joins_group', 'newsletter')
 *       ->delay(1, 'day')
 *       ->sendEmail('welcome-template')
 *       ->delay(3, 'days')
 *       ->sendEmail('tips-template')
 *       ->start();
 */
class AutomationBuilder
{
    /**
     * Current automation name being built
     */
    protected ?string $name = null;

    /**
     * Current automation description being built
     */
    protected ?string $description = null;

    /**
     * Whether the automation is enabled
     */
    protected bool $enabled = true;

    /**
     * Current automation status
     */
    protected string $status = 'draft';

    /**
     * Current automation triggers being built
     */
    protected array $triggers = [];

    /**
     * Current automation steps being built
     */
    protected array $steps = [];

    /**
     * Current automation settings being built
     */
    protected array $settings = [];

    /**
     * Current automation conditions being built
     */
    protected array $conditions = [];

    /**
     * Create a new automation builder instance.
     */
    public function __construct(
        protected AutomationService $service
    ) {}

    /**
     * Start creating a new automation with a name.
     */
    public function create(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the automation name.
     */
    public function named(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the automation description.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the automation as enabled.
     */
    public function enabled(bool $enabled = true): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Set the automation as disabled.
     */
    public function disabled(): static
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Set the automation status.
     */
    public function status(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Add a subscriber trigger.
     */
    public function trigger(string $type, string $event, ?string $target = null): static
    {
        $trigger = [
            'type' => $type,
            'event' => $event,
        ];

        if ($target !== null) {
            $trigger['target'] = $target;
        }

        $this->triggers[] = $trigger;

        return $this;
    }

    /**
     * Add a subscriber joins group trigger.
     */
    public function whenSubscriberJoinsGroup(string $groupId): static
    {
        return $this->trigger('subscriber', 'joins_group', $groupId);
    }

    /**
     * Add a subscriber subscribes trigger.
     */
    public function whenSubscriberSubscribes(): static
    {
        return $this->trigger('subscriber', 'subscribes');
    }

    /**
     * Add a subscriber updates field trigger.
     */
    public function whenSubscriberUpdatesField(string $fieldName): static
    {
        return $this->trigger('subscriber', 'updates_field', $fieldName);
    }

    /**
     * Add a date-based trigger.
     */
    public function whenDateReached(string $dateField, int $offsetDays = 0): static
    {
        $trigger = [
            'type' => 'date',
            'field' => $dateField,
            'offset' => $offsetDays,
            'unit' => 'days',
        ];

        $this->triggers[] = $trigger;

        return $this;
    }

    /**
     * Add an API trigger.
     */
    public function whenApiCalled(string $endpoint): static
    {
        $trigger = [
            'type' => 'api',
            'endpoint' => $endpoint,
        ];

        $this->triggers[] = $trigger;

        return $this;
    }

    /**
     * Add a webhook trigger.
     */
    public function whenWebhookReceived(string $endpoint): static
    {
        $trigger = [
            'type' => 'webhook',
            'endpoint' => $endpoint,
        ];

        $this->triggers[] = $trigger;

        return $this;
    }

    /**
     * Add an email step.
     */
    public function sendEmail(string $templateId): static
    {
        $step = [
            'type' => 'email',
            'template_id' => $templateId,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add an email step with campaign ID.
     */
    public function sendCampaign(string $campaignId): static
    {
        $step = [
            'type' => 'email',
            'campaign_id' => $campaignId,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a delay step.
     */
    public function delay(int $duration, string $unit = 'days'): static
    {
        $step = [
            'type' => 'delay',
            'duration' => $duration,
            'unit' => $unit,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a delay in minutes.
     */
    public function delayMinutes(int $minutes): static
    {
        return $this->delay($minutes, 'minutes');
    }

    /**
     * Add a delay in hours.
     */
    public function delayHours(int $hours): static
    {
        return $this->delay($hours, 'hours');
    }

    /**
     * Add a delay in days.
     */
    public function delayDays(int $days): static
    {
        return $this->delay($days, 'days');
    }

    /**
     * Add a delay in weeks.
     */
    public function delayWeeks(int $weeks): static
    {
        return $this->delay($weeks, 'weeks');
    }

    /**
     * Add a condition step.
     */
    public function condition(array $conditions): static
    {
        $step = [
            'type' => 'condition',
            'conditions' => $conditions,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a field condition.
     */
    public function ifField(string $field, string $operator, mixed $value): static
    {
        return $this->condition([
            [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Add a tag action step.
     */
    public function addTag(string $tag): static
    {
        $step = [
            'type' => 'tag',
            'action' => 'add',
            'tag' => $tag,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a remove tag action step.
     */
    public function removeTag(string $tag): static
    {
        $step = [
            'type' => 'tag',
            'action' => 'remove',
            'tag' => $tag,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a field update step.
     */
    public function updateField(string $field, mixed $value): static
    {
        $step = [
            'type' => 'field_update',
            'field' => $field,
            'value' => $value,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add a webhook action step.
     */
    public function callWebhook(string $url, array $data = []): static
    {
        $step = [
            'type' => 'webhook',
            'url' => $url,
            'data' => $data,
        ];

        $this->steps[] = $step;

        return $this;
    }

    /**
     * Add automation settings.
     */
    public function withSettings(array $settings): static
    {
        $this->settings = array_merge($this->settings, $settings);

        return $this;
    }

    /**
     * Add a single automation setting.
     */
    public function withSetting(string $key, mixed $value): static
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Set timezone setting.
     */
    public function timezone(string $timezone): static
    {
        return $this->withSetting('timezone', $timezone);
    }

    /**
     * Set send time restrictions.
     */
    public function sendTimeBetween(string $startTime, string $endTime): static
    {
        return $this->withSetting('send_time', [
            'start' => $startTime,
            'end' => $endTime,
        ]);
    }

    /**
     * Set frequency cap.
     */
    public function frequencyCap(int $cap): static
    {
        return $this->withSetting('frequency_cap', $cap);
    }

    /**
     * Add automation conditions.
     */
    public function withConditions(array $conditions): static
    {
        $this->conditions = array_merge($this->conditions, $conditions);

        return $this;
    }

    /**
     * Add a single automation condition.
     */
    public function withCondition(string $field, string $operator, mixed $value): static
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Create and save the automation.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationCreateException
     * @throws \InvalidArgumentException
     */
    public function save(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Create and start the automation.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationCreateException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     * @throws \InvalidArgumentException
     */
    public function start(): array
    {
        // First create the automation
        $automation = $this->save();

        // Then start it
        return $this->service->start($automation['id']);
    }

    /**
     * Find automation by ID.
     */
    public function find(string $id): ?array
    {
        return $this->service->getById($id);
    }

    /**
     * Find automation by name.
     */
    public function findByName(string $name): ?array
    {
        return $this->service->findByName($name);
    }

    /**
     * Update an existing automation.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationUpdateException
     */
    public function update(string $id): array
    {
        $dto = $this->toDTO();

        return $this->service->update($id, $dto);
    }

    /**
     * Delete an automation.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationDeleteException
     */
    public function delete(string $id): bool
    {
        return $this->service->delete($id);
    }

    /**
     * Get automations list with filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all automations (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Start an existing automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function startById(string $id): array
    {
        return $this->service->start($id);
    }

    /**
     * Stop an existing automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function stopById(string $id): array
    {
        return $this->service->stop($id);
    }

    /**
     * Enable an existing automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function enableById(string $id): array
    {
        return $this->service->enable($id);
    }

    /**
     * Disable an existing automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function disableById(string $id): array
    {
        return $this->service->disable($id);
    }

    /**
     * Pause an existing automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function pauseById(string $id): array
    {
        return $this->service->pause($id);
    }

    /**
     * Resume a paused automation by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationStateException
     */
    public function resumeById(string $id): array
    {
        return $this->service->resume($id);
    }

    /**
     * Get automation statistics.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     */
    public function stats(string $id): array
    {
        return $this->service->getStats($id);
    }

    /**
     * Get automation subscribers.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     */
    public function subscribers(string $id, array $filters = []): array
    {
        return $this->service->getSubscribers($id, $filters);
    }

    /**
     * Get automation activity.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException
     */
    public function activity(string $id, array $filters = []): array
    {
        return $this->service->getActivity($id, $filters);
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): AutomationDTO
    {
        if (! $this->name) {
            throw new \InvalidArgumentException('Name is required to create AutomationDTO');
        }

        return new AutomationDTO(
            name: $this->name,
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
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->name = null;
        $this->description = null;
        $this->enabled = true;
        $this->status = 'draft';
        $this->triggers = [];
        $this->steps = [];
        $this->settings = [];
        $this->conditions = [];

        return $this;
    }

    /**
     * Create a new builder instance from this one.
     */
    public function fresh(): static
    {
        return new static($this->service);
    }

    /**
     * Magic method to handle method chaining with "and" for readability.
     *
     * Examples:
     *   ->create('Welcome')->andDescription('Welcome new users')
     *   ->sendEmail('template')->andDelay(1, 'day')
     */
    public function __call(string $method, array $arguments): mixed
    {
        // Handle "and" prefixed methods for natural language chaining
        if (str_starts_with($method, 'and')) {
            $actualMethod = lcfirst(substr($method, 3));

            if (method_exists($this, $actualMethod)) {
                return $this->$actualMethod(...$arguments);
            }
        }

        // Handle "then" prefixed methods for natural language chaining
        if (str_starts_with($method, 'then')) {
            $actualMethod = lcfirst(substr($method, 4));

            if (method_exists($this, $actualMethod)) {
                return $this->$actualMethod(...$arguments);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist on ".static::class);
    }
}
