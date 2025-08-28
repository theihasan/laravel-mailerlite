<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Webhooks;

use Ihasan\LaravelMailerlite\DTOs\WebhookDTO;

/**
 * Webhook Builder
 *
 * Provides a fluent, plain-English API for building webhook operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::webhooks()
 *       ->on('subscriber.created')
 *       ->url('https://yourapp.com/webhooks/mailerlite')
 *       ->withSecret('your-secret-key')
 *       ->create();
 */
class WebhookBuilder
{
    /**
     * Current webhook event being built
     */
    protected ?string $event = null;

    /**
     * Current webhook URL being built
     */
    protected ?string $url = null;

    /**
     * Whether the webhook is enabled
     */
    protected bool $enabled = true;

    /**
     * Current webhook name being built
     */
    protected ?string $name = null;

    /**
     * Current webhook settings being built
     */
    protected array $settings = [];

    /**
     * Current webhook headers being built
     */
    protected array $headers = [];

    /**
     * Current webhook secret being built
     */
    protected ?string $secret = null;

    /**
     * Current webhook timeout being built
     */
    protected int $timeout = 30;

    /**
     * Current webhook retry count being built
     */
    protected int $retryCount = 3;

    /**
     * Create a new webhook builder instance.
     */
    public function __construct(
        protected WebhookService $service
    ) {}

    /**
     * Set the webhook event to listen for.
     */
    public function on(string $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Alias for on() - more natural in some contexts.
     */
    public function listen(string $event): static
    {
        return $this->on($event);
    }

    /**
     * Set the webhook URL endpoint.
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Alias for url() - more natural in some contexts.
     */
    public function to(string $url): static
    {
        return $this->url($url);
    }

    /**
     * Set the webhook name.
     */
    public function named(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the webhook as enabled.
     */
    public function enabled(bool $enabled = true): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Set the webhook as disabled.
     */
    public function disabled(): static
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Add webhook settings.
     */
    public function withSettings(array $settings): static
    {
        $this->settings = array_merge($this->settings, $settings);

        return $this;
    }

    /**
     * Add a single webhook setting.
     */
    public function withSetting(string $key, mixed $value): static
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Add webhook headers.
     */
    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Add a single webhook header.
     */
    public function withHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Set webhook secret for signature verification.
     */
    public function withSecret(string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Set webhook timeout.
     */
    public function timeout(int $timeout): static
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Set webhook retry count.
     */
    public function retries(int $retryCount): static
    {
        $this->retryCount = $retryCount;

        return $this;
    }

    /**
     * Enable SSL verification.
     */
    public function verifySSL(bool $verify = true): static
    {
        return $this->withSetting('verify_ssl', $verify);
    }

    /**
     * Set content type.
     */
    public function contentType(string $contentType): static
    {
        return $this->withSetting('content_type', $contentType);
    }

    /**
     * Set content type to JSON.
     */
    public function asJson(): static
    {
        return $this->contentType('application/json');
    }

    /**
     * Set content type to form data.
     */
    public function asForm(): static
    {
        return $this->contentType('application/x-www-form-urlencoded');
    }

    /**
     * Listen for subscriber events.
     */
    public function onSubscriber(string $event): static
    {
        $validEvents = ['created', 'updated', 'unsubscribed', 'bounced', 'complained', 'deleted'];
        
        if (! in_array($event, $validEvents, true)) {
            throw new \InvalidArgumentException("Invalid subscriber event '{$event}'. Valid events: " . implode(', ', $validEvents));
        }

        return $this->on("subscriber.{$event}");
    }

    /**
     * Listen for campaign events.
     */
    public function onCampaign(string $event): static
    {
        $validEvents = ['sent', 'opened', 'clicked', 'bounced', 'complained', 'unsubscribed', 'delivered', 'soft_bounced', 'hard_bounced'];
        
        if (! in_array($event, $validEvents, true)) {
            throw new \InvalidArgumentException("Invalid campaign event '{$event}'. Valid events: " . implode(', ', $validEvents));
        }

        return $this->on("campaign.{$event}");
    }

    /**
     * Listen for automation events.
     */
    public function onAutomation(string $event): static
    {
        $validEvents = ['subscriber_added', 'subscriber_completed', 'email_sent', 'started', 'stopped'];
        
        if (! in_array($event, $validEvents, true)) {
            throw new \InvalidArgumentException("Invalid automation event '{$event}'. Valid events: " . implode(', ', $validEvents));
        }

        return $this->on("automation.{$event}");
    }

    /**
     * Listen for form events.
     */
    public function onForm(string $event): static
    {
        $validEvents = ['submitted'];
        
        if (! in_array($event, $validEvents, true)) {
            throw new \InvalidArgumentException("Invalid form event '{$event}'. Valid events: " . implode(', ', $validEvents));
        }

        return $this->on("form.{$event}");
    }

    /**
     * Listen for group events.
     */
    public function onGroup(string $event): static
    {
        $validEvents = ['subscriber_added', 'subscriber_removed'];
        
        if (! in_array($event, $validEvents, true)) {
            throw new \InvalidArgumentException("Invalid group event '{$event}'. Valid events: " . implode(', ', $validEvents));
        }

        return $this->on("group.{$event}");
    }

    /**
     * Listen for webhook test events.
     */
    public function onTest(): static
    {
        return $this->on('webhook.test');
    }

    /**
     * Create and save the webhook.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookCreateException
     * @throws \InvalidArgumentException
     */
    public function create(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Find webhook by ID.
     */
    public function find(string $id): ?array
    {
        return $this->service->getById($id);
    }

    /**
     * Update an existing webhook.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookUpdateException
     */
    public function update(string $id): array
    {
        $dto = $this->toDTO();

        return $this->service->update($id, $dto);
    }

    /**
     * Delete a webhook.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookDeleteException
     */
    public function delete(string $id): bool
    {
        return $this->service->delete($id);
    }

    /**
     * Get webhooks list with filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all webhooks (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Enable a webhook by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookUpdateException
     */
    public function enable(string $id): array
    {
        return $this->service->enable($id);
    }

    /**
     * Disable a webhook by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookUpdateException
     */
    public function disable(string $id): array
    {
        return $this->service->disable($id);
    }

    /**
     * Test a webhook by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     */
    public function test(string $id): array
    {
        return $this->service->test($id);
    }

    /**
     * Get webhook delivery logs.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     */
    public function logs(string $id, array $filters = []): array
    {
        return $this->service->getLogs($id, $filters);
    }

    /**
     * Get webhook statistics.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     */
    public function stats(string $id): array
    {
        return $this->service->getStats($id);
    }

    /**
     * Find webhook by URL and event.
     */
    public function findByUrl(string $url, ?string $event = null): ?array
    {
        return $this->service->findByUrl($url, $event);
    }

    /**
     * Delete webhook by URL and event.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\WebhookDeleteException
     */
    public function deleteByUrl(string $url, ?string $event = null): bool
    {
        return $this->service->deleteByUrl($url, $event);
    }

    /**
     * Create webhook for subscriber created event.
     */
    public function onSubscriberCreated(string $url): static
    {
        return $this->onSubscriber('created')->url($url);
    }

    /**
     * Create webhook for subscriber updated event.
     */
    public function onSubscriberUpdated(string $url): static
    {
        return $this->onSubscriber('updated')->url($url);
    }

    /**
     * Create webhook for subscriber unsubscribed event.
     */
    public function onSubscriberUnsubscribed(string $url): static
    {
        return $this->onSubscriber('unsubscribed')->url($url);
    }

    /**
     * Create webhook for campaign sent event.
     */
    public function onCampaignSent(string $url): static
    {
        return $this->onCampaign('sent')->url($url);
    }

    /**
     * Create webhook for campaign opened event.
     */
    public function onCampaignOpened(string $url): static
    {
        return $this->onCampaign('opened')->url($url);
    }

    /**
     * Create webhook for campaign clicked event.
     */
    public function onCampaignClicked(string $url): static
    {
        return $this->onCampaign('clicked')->url($url);
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): WebhookDTO
    {
        if (! $this->event) {
            throw new \InvalidArgumentException('Event is required to create WebhookDTO');
        }

        if (! $this->url) {
            throw new \InvalidArgumentException('URL is required to create WebhookDTO');
        }

        return new WebhookDTO(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $this->name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $this->secret,
            timeout: $this->timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->event = null;
        $this->url = null;
        $this->enabled = true;
        $this->name = null;
        $this->settings = [];
        $this->headers = [];
        $this->secret = null;
        $this->timeout = 30;
        $this->retryCount = 3;

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
     *   ->on('subscriber.created')->andUrl('https://example.com')
     *   ->withSecret('secret')->andTimeout(60)
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
