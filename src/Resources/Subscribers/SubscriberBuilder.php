<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Subscribers;

use Ihasan\LaravelMailerlite\DTOs\SubscriberDTO;

/**
 * Subscriber Builder
 *
 * Provides a fluent, plain-English API for building subscriber operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::subscribers()
 *       ->email('user@example.com')
 *       ->named('John Doe')
 *       ->subscribe()
 *       ->toGroup('Newsletter');
 */
class SubscriberBuilder
{
    /**
     * Current subscriber email being built
     */
    protected ?string $email = null;

    /**
     * Current subscriber name being built
     */
    protected ?string $name = null;

    /**
     * Current subscriber fields being built
     */
    protected array $fields = [];

    /**
     * Current subscriber groups being built
     */
    protected array $groups = [];

    /**
     * Current subscriber status being built
     */
    protected string $status = 'active';

    /**
     * Whether to resubscribe if subscriber already exists
     */
    protected bool $resubscribe = false;

    /**
     * Subscriber type
     */
    protected ?string $type = null;

    /**
     * Current subscriber segments
     */
    protected array $segments = [];

    /**
     * Whether to send autoresponders
     */
    protected bool $autoresponders = true;

    /**
     * Create a new subscriber builder instance.
     */
    public function __construct(
        protected SubscriberService $service
    ) {}

    /**
     * Set the subscriber email address.
     */
    public function email(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set the subscriber name.
     */
    public function named(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alias for named() - more natural in some contexts.
     */
    public function withName(string $name): static
    {
        return $this->named($name);
    }

    /**
     * Set custom fields for the subscriber.
     */
    public function withFields(array $fields): static
    {
        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * Add a single custom field.
     */
    public function withField(string $key, mixed $value): static
    {
        $this->fields[$key] = $value;

        return $this;
    }

    /**
     * Add the subscriber to groups.
     */
    public function toGroup(string|array $groups): static
    {
        if (is_string($groups)) {
            $this->groups[] = $groups;
        } else {
            $this->groups = array_merge($this->groups, $groups);
        }

        return $this;
    }

    /**
     * Alias for toGroup() - more natural when adding multiple groups.
     */
    public function toGroups(string|array $groups): static
    {
        return $this->toGroup($groups);
    }

    /**
     * Add the subscriber to segments.
     */
    public function toSegment(string|array $segments): static
    {
        if (is_string($segments)) {
            $this->segments[] = $segments;
        } else {
            $this->segments = array_merge($this->segments, $segments);
        }

        return $this;
    }

    /**
     * Alias for toSegment() - more natural when adding multiple segments.
     */
    public function toSegments(string|array $segments): static
    {
        return $this->toSegment($segments);
    }

    /**
     * Set the subscriber as active (default status).
     */
    public function active(): static
    {
        $this->status = 'active';

        return $this;
    }

    /**
     * Set the subscriber as unsubscribed.
     */
    public function unsubscribed(): static
    {
        $this->status = 'unsubscribed';

        return $this;
    }

    /**
     * Set the subscriber as unconfirmed.
     */
    public function unconfirmed(): static
    {
        $this->status = 'unconfirmed';

        return $this;
    }

    /**
     * Enable resubscribe if subscriber already exists.
     */
    public function resubscribeIfExists(): static
    {
        $this->resubscribe = true;

        return $this;
    }

    /**
     * Set subscriber type to imported.
     */
    public function imported(): static
    {
        $this->type = 'imported';

        return $this;
    }

    /**
     * Set subscriber type to regular.
     */
    public function regular(): static
    {
        $this->type = 'regular';

        return $this;
    }

    /**
     * Disable autoresponders for this subscriber.
     */
    public function withoutAutoresponders(): static
    {
        $this->autoresponders = false;

        return $this;
    }

    /**
     * Enable autoresponders for this subscriber (default).
     */
    public function withAutoresponders(): static
    {
        $this->autoresponders = true;

        return $this;
    }

    /**
     * Create/subscribe the subscriber.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberCreateException
     * @throws \InvalidArgumentException
     */
    public function subscribe(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Alias for subscribe() - more natural in some contexts.
     */
    public function create(): array
    {
        return $this->subscribe();
    }

    /**
     * Find subscriber by current email and update.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberUpdateException
     */
    public function update(): ?array
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to update subscriber');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return null;
        }

        $dto = $this->toDTO();

        return $this->service->update($existing['id'], $dto);
    }

    /**
     * Update a subscriber by explicit MailerLite ID.
     *
     * This bypasses the email lookup and is useful when the ID is already
     * known (e.g., stored locally) and we simply want to push the current
     * builder state to MailerLite.
     */
    public function updateById(string $id): array
    {
        $dto = $this->toDTO();

        return $this->service->update($id, $dto);
    }

    /**
     * Find subscriber by current email.
     */
    public function find(): ?array
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to find subscriber');
        }

        return $this->service->getByEmail($this->email);
    }

    /**
     * Find subscriber by current email and unsubscribe.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberUpdateException
     */
    public function unsubscribe(): ?array
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to unsubscribe subscriber');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return null;
        }

        return $this->service->unsubscribe($existing['id']);
    }

    /**
     * Find subscriber by current email and resubscribe.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberUpdateException
     */
    public function resubscribe(): ?array
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to resubscribe subscriber');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return null;
        }

        return $this->service->resubscribe($existing['id']);
    }

    /**
     * Find subscriber by current email and delete.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberDeleteException
     */
    public function delete(): bool
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to delete subscriber');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return false;
        }

        return $this->service->delete($existing['id']);
    }

    /**
     * Find subscriber by current email and add to a group.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     */
    public function addToGroup(string $groupId): ?array
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to add subscriber to group');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return null;
        }

        return $this->service->addToGroup($existing['id'], $groupId);
    }

    /**
     * Find subscriber by current email and remove from a group.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\SubscriberNotFoundException
     */
    public function removeFromGroup(string $groupId): bool
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to remove subscriber from group');
        }

        $existing = $this->service->getByEmail($this->email);
        if (! $existing) {
            return false;
        }

        return $this->service->removeFromGroup($existing['id'], $groupId);
    }

    /**
     * Get subscribers list with filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all subscribers (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): SubscriberDTO
    {
        if (! $this->email) {
            throw new \InvalidArgumentException('Email is required to create SubscriberDTO');
        }

        return new SubscriberDTO(
            email: $this->email,
            name: $this->name,
            fields: $this->fields,
            groups: array_unique($this->groups),
            status: $this->status,
            resubscribe: $this->resubscribe,
            type: $this->type,
            segments: array_unique($this->segments),
            autoresponders: $this->autoresponders,
        );
    }

    /**
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->email = null;
        $this->name = null;
        $this->fields = [];
        $this->groups = [];
        $this->status = 'active';
        $this->resubscribe = false;
        $this->type = null;
        $this->segments = [];
        $this->autoresponders = true;

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
     *   ->email('user@test.com')->andNamed('John')
     *   ->withField('role', 'admin')->andToGroup('Admins')
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
