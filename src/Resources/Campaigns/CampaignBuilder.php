<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Campaigns;

use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;

/**
 * Campaign Builder
 *
 * Provides a fluent, plain-English API for building campaign operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::campaigns()
 *       ->draft()
 *       ->subject('Weekly Newsletter')
 *       ->from('Newsletter', 'newsletter@example.com')
 *       ->html('<h1>Hello World</h1>')
 *       ->toGroup('Newsletter Subscribers')
 *       ->scheduleAt(new DateTime('+1 hour'))
 *       ->send();
 */
class CampaignBuilder
{
    /**
     * Current campaign subject being built
     */
    protected ?string $subject = null;

    /**
     * Current campaign from name being built
     */
    protected ?string $fromName = null;

    /**
     * Current campaign from email being built
     */
    protected ?string $fromEmail = null;

    /**
     * Current campaign HTML content being built
     */
    protected ?string $html = null;

    /**
     * Current campaign plain text content being built
     */
    protected ?string $plain = null;

    /**
     * Current campaign groups being built
     */
    protected array $groups = [];

    /**
     * Current campaign segments being built
     */
    protected array $segments = [];

    /**
     * Current campaign schedule time being built
     */
    protected ?\DateTimeInterface $scheduleAt = null;

    /**
     * Current campaign type being built
     */
    protected string $type = 'regular';

    /**
     * Current campaign settings being built
     */
    protected array $settings = [];

    /**
     * Current campaign A/B settings being built
     */
    protected array $abSettings = [];

    /**
     * Whether this is a draft campaign
     */
    protected bool $isDraft = false;

    /**
     * Create a new campaign builder instance.
     */
    public function __construct(
        protected CampaignService $service
    ) {}

    /**
     * Start building a draft campaign.
     */
    public function draft(): static
    {
        $this->isDraft = true;

        return $this;
    }

    /**
     * Set the campaign subject.
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set the sender information.
     */
    public function from(string $fromName, string $fromEmail): static
    {
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Set the sender name only.
     */
    public function fromName(string $fromName): static
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * Set the sender email only.
     */
    public function fromEmail(string $fromEmail): static
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * Set the HTML content.
     */
    public function html(string $html): static
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Set the plain text content.
     */
    public function plain(string $plain): static
    {
        $this->plain = $plain;

        return $this;
    }

    /**
     * Set both HTML and plain text content.
     */
    public function content(string $html, string $plain = null): static
    {
        $this->html = $html;
        if ($plain !== null) {
            $this->plain = $plain;
        }

        return $this;
    }

    /**
     * Add the campaign to a group.
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
     * Add the campaign to a segment.
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
     * Schedule the campaign to be sent at a specific time.
     */
    public function scheduleAt(\DateTimeInterface $scheduleAt): static
    {
        $this->scheduleAt = $scheduleAt;

        return $this;
    }

    /**
     * Schedule the campaign to be sent in a certain number of minutes.
     */
    public function scheduleIn(int $minutes): static
    {
        $this->scheduleAt = new \DateTime("+{$minutes} minutes");

        return $this;
    }

    /**
     * Schedule the campaign to be sent at a specific date and time.
     */
    public function scheduleFor(string $dateTime): static
    {
        $this->scheduleAt = new \DateTime($dateTime);

        return $this;
    }

    /**
     * Set the campaign type to regular.
     */
    public function regular(): static
    {
        $this->type = 'regular';

        return $this;
    }

    /**
     * Set the campaign type to A/B test.
     */
    public function abTest(array $settings = []): static
    {
        $this->type = 'ab';
        $this->abSettings = $settings;

        return $this;
    }

    /**
     * Set the campaign type to resend.
     */
    public function resend(): static
    {
        $this->type = 'resend';

        return $this;
    }

    /**
     * Add campaign settings.
     */
    public function withSettings(array $settings): static
    {
        $this->settings = array_merge($this->settings, $settings);

        return $this;
    }

    /**
     * Add a single campaign setting.
     */
    public function withSetting(string $key, mixed $value): static
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Create the campaign.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignCreateException
     * @throws \InvalidArgumentException
     */
    public function create(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Send the campaign immediately.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignCreateException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignSendException
     * @throws \InvalidArgumentException
     */
    public function send(): array
    {
        // First create the campaign
        $campaign = $this->create();

        // Then send it immediately
        return $this->service->send($campaign['id']);
    }

    /**
     * Schedule the campaign (create and schedule).
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignCreateException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignSendException
     * @throws \InvalidArgumentException
     */
    public function schedule(): array
    {
        if ($this->scheduleAt === null) {
            throw new \InvalidArgumentException('Schedule time is required to schedule campaign');
        }

        // First create the campaign
        $campaign = $this->create();

        // Then schedule it
        return $this->service->schedule($campaign['id'], $this->scheduleAt);
    }

    /**
     * Find campaign by ID.
     */
    public function find(string $id): ?array
    {
        return $this->service->getById($id);
    }

    /**
     * Update an existing campaign.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignUpdateException
     */
    public function update(string $id): array
    {
        $dto = $this->toDTO();

        return $this->service->update($id, $dto);
    }

    /**
     * Delete a campaign.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignDeleteException
     */
    public function delete(string $id): bool
    {
        return $this->service->delete($id);
    }

    /**
     * Get campaigns list with filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all campaigns (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Get campaign statistics.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     */
    public function stats(string $id): array
    {
        return $this->service->getStats($id);
    }

    /**
     * Get campaign subscribers.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     */
    public function subscribers(string $id, array $filters = []): array
    {
        return $this->service->getSubscribers($id, $filters);
    }

    /**
     * Send an existing campaign by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignSendException
     */
    public function sendById(string $id): array
    {
        return $this->service->send($id);
    }

    /**
     * Schedule an existing campaign by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignSendException
     */
    public function scheduleById(string $id, \DateTimeInterface $scheduleAt): array
    {
        return $this->service->schedule($id, $scheduleAt);
    }

    /**
     * Cancel a scheduled campaign by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\CampaignUpdateException
     */
    public function cancel(string $id): array
    {
        return $this->service->cancel($id);
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): CampaignDTO
    {
        if (! $this->subject) {
            throw new \InvalidArgumentException('Subject is required to create CampaignDTO');
        }

        if (! $this->fromName) {
            throw new \InvalidArgumentException('From name is required to create CampaignDTO');
        }

        if (! $this->fromEmail) {
            throw new \InvalidArgumentException('From email is required to create CampaignDTO');
        }

        return new CampaignDTO(
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: array_unique($this->groups),
            segments: array_unique($this->segments),
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->subject = null;
        $this->fromName = null;
        $this->fromEmail = null;
        $this->html = null;
        $this->plain = null;
        $this->groups = [];
        $this->segments = [];
        $this->scheduleAt = null;
        $this->type = 'regular';
        $this->settings = [];
        $this->abSettings = [];
        $this->isDraft = false;

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
     *   ->subject('Test')->andFrom('John', 'john@test.com')
     *   ->html('<h1>Hello</h1>')->andToGroup('Newsletter')
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

        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }
}
