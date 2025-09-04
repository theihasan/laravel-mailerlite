<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Segments;

use Ihasan\LaravelMailerlite\DTOs\SegmentDTO;

/**
 * Segment Builder
 *
 * Provides a fluent, plain-English API for building segment operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::segments()
 *       ->name('Active Users')
 *       ->whereField('last_login', 'after', '2023-01-01')
 *       ->andWhereGroup('premium_users', true)
 *       ->create();
 */
class SegmentBuilder
{
    /**
     * Current segment name being built
     */
    protected ?string $name = null;

    /**
     * Current segment description being built
     */
    protected ?string $description = null;

    /**
     * Current segment filters being built
     */
    protected array $filters = [];

    /**
     * Current segment tags being built
     */
    protected array $tags = [];

    /**
     * Current segment options being built
     */
    protected array $options = [];

    /**
     * Whether the segment is active
     */
    protected bool $active = true;

    /**
     * Create a new segment builder instance.
     */
    public function __construct(
        protected SegmentService $service
    ) {}

    /**
     * Set the segment name.
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alias for name() - more natural in some contexts.
     */
    public function named(string $name): static
    {
        return $this->name($name);
    }

    /**
     * Set the segment description.
     */
    public function withDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Alias for withDescription() - shorter version.
     */
    public function description(string $description): static
    {
        return $this->withDescription($description);
    }

    /**
     * Add a field filter condition.
     */
    public function whereField(string $fieldName, string $operator, mixed $value): static
    {
        $this->filters[] = [
            'type' => 'field',
            'field' => $fieldName,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add another field filter condition (AND logic).
     */
    public function andWhereField(string $fieldName, string $operator, mixed $value): static
    {
        return $this->whereField($fieldName, $operator, $value);
    }

    /**
     * Add a group membership filter.
     */
    public function whereGroup(string $groupId, bool $isMember = true): static
    {
        $this->filters[] = [
            'type' => 'group',
            'group_id' => $groupId,
            'operator' => $isMember ? 'in' : 'not_in',
        ];

        return $this;
    }

    /**
     * Add another group membership filter (AND logic).
     */
    public function andWhereGroup(string $groupId, bool $isMember = true): static
    {
        return $this->whereGroup($groupId, $isMember);
    }

    /**
     * Add a date-based filter.
     */
    public function whereDate(string $dateField, string $operator, string|array $value): static
    {
        $this->filters[] = [
            'type' => 'date',
            'field' => $dateField,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add another date-based filter (AND logic).
     */
    public function andWhereDate(string $dateField, string $operator, string|array $value): static
    {
        return $this->whereDate($dateField, $operator, $value);
    }

    /**
     * Add an email activity filter.
     */
    public function whereEmailActivity(string $activity, ?string $campaignId = null, ?int $days = null): static
    {
        $filter = [
            'type' => 'email_activity',
            'activity' => $activity,
        ];

        if ($campaignId) {
            $filter['campaign_id'] = $campaignId;
        }

        if ($days) {
            $filter['days'] = $days;
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Add another email activity filter (AND logic).
     */
    public function andWhereEmailActivity(string $activity, ?string $campaignId = null, ?int $days = null): static
    {
        return $this->whereEmailActivity($activity, $campaignId, $days);
    }

    // Convenient methods for common conditions

    /**
     * Filter subscribers who opened emails.
     */
    public function whoOpened(?string $campaignId = null, ?int $days = null): static
    {
        return $this->whereEmailActivity('opened', $campaignId, $days);
    }

    /**
     * Filter subscribers who clicked emails.
     */
    public function whoClicked(?string $campaignId = null, ?int $days = null): static
    {
        return $this->whereEmailActivity('clicked', $campaignId, $days);
    }

    /**
     * Filter subscribers who didn't open emails.
     */
    public function whoDidntOpen(?string $campaignId = null, ?int $days = null): static
    {
        return $this->whereEmailActivity('not_opened', $campaignId, $days);
    }

    /**
     * Filter subscribers who didn't click emails.
     */
    public function whoDidntClick(?string $campaignId = null, ?int $days = null): static
    {
        return $this->whereEmailActivity('not_clicked', $campaignId, $days);
    }

    /**
     * Filter subscribers in a specific group.
     */
    public function inGroup(string $groupId): static
    {
        return $this->whereGroup($groupId, true);
    }

    /**
     * Filter subscribers not in a specific group.
     */
    public function notInGroup(string $groupId): static
    {
        return $this->whereGroup($groupId, false);
    }

    /**
     * Filter subscribers created after a date.
     */
    public function createdAfter(string $date): static
    {
        return $this->whereDate('created_at', 'after', $date);
    }

    /**
     * Filter subscribers created before a date.
     */
    public function createdBefore(string $date): static
    {
        return $this->whereDate('created_at', 'before', $date);
    }

    /**
     * Filter subscribers subscribed after a date.
     */
    public function subscribedAfter(string $date): static
    {
        return $this->whereDate('subscribed_at', 'after', $date);
    }

    /**
     * Filter subscribers subscribed before a date.
     */
    public function subscribedBefore(string $date): static
    {
        return $this->whereDate('subscribed_at', 'before', $date);
    }

    /**
     * Add tags to the segment.
     */
    public function withTags(string|array $tags): static
    {
        if (is_string($tags)) {
            $this->tags[] = $tags;
        } else {
            $this->tags = array_merge($this->tags, $tags);
        }

        return $this;
    }

    /**
     * Add a single tag to the segment.
     */
    public function withTag(string $tag): static
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Alias for withTag() - more natural in some contexts.
     */
    public function tagged(string $tag): static
    {
        return $this->withTag($tag);
    }

    /**
     * Add options to the segment.
     */
    public function withOptions(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Add a single option to the segment.
     */
    public function withOption(string $key, mixed $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set the segment as active.
     */
    public function active(): static
    {
        $this->active = true;

        return $this;
    }

    /**
     * Set the segment as inactive.
     */
    public function inactive(): static
    {
        $this->active = false;

        return $this;
    }

    /**
     * Create the segment.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentCreateException
     * @throws \InvalidArgumentException
     */
    public function create(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Update a segment by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentUpdateException
     */
    public function update(string $id): array
    {
        $dto = $this->toDTO();

        return $this->service->update($id, $dto);
    }

    /**
     * Delete a segment by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentDeleteException
     */
    public function delete(string $id): bool
    {
        return $this->service->delete($id);
    }

    /**
     * Get a segment by ID.
     */
    public function find(string $id): ?array
    {
        return $this->service->get($id);
    }

    /**
     * Get all segments with optional filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all segments (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Get subscribers in a segment.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     */
    public function getSubscribers(string $segmentId, array $filters = []): array
    {
        return $this->service->getSubscribers($segmentId, $filters);
    }

    /**
     * Refresh/recalculate a segment.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     */
    public function refresh(string $segmentId): array
    {
        return $this->service->refresh($segmentId);
    }

    /**
     * Get segment statistics.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     */
    public function getStats(string $segmentId): array
    {
        return $this->service->getStats($segmentId);
    }

    /**
     * Activate a segment.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentUpdateException
     */
    public function activate(string $segmentId): array
    {
        return $this->service->activate($segmentId);
    }

    /**
     * Deactivate a segment.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SegmentUpdateException
     */
    public function deactivate(string $segmentId): array
    {
        return $this->service->deactivate($segmentId);
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): SegmentDTO
    {
        if (! $this->name) {
            throw new \InvalidArgumentException('Name is required to create SegmentDTO');
        }

        if (empty($this->filters)) {
            throw new \InvalidArgumentException('At least one filter is required to create SegmentDTO');
        }

        return new SegmentDTO(
            name: $this->name,
            filters: $this->filters,
            description: $this->description,
            tags: array_unique($this->tags),
            options: $this->options,
            active: $this->active,
        );
    }

    /**
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->name = null;
        $this->description = null;
        $this->filters = [];
        $this->tags = [];
        $this->options = [];
        $this->active = true;

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
     *   ->name('Active Users')->andWhereField('active', 'equals', true)
     *   ->whereGroup('premium')->andWhoOpened('campaign123')
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

        throw new \BadMethodCallException("Method {$method} does not exist on ".static::class);
    }
}
