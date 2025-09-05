<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\DTOs;

use InvalidArgumentException;

/**
 * Webhook Data Transfer Object
 *
 * This class represents webhook data with validation and normalization.
 * It ensures that webhook information is properly formatted and valid
 * before being sent to the MailerLite API.
 */
class WebhookDTO
{
    /**
     * Create a new webhook DTO.
     *
     * @param  string  $event  Webhook event to listen for (required)
     * @param  string  $url  Webhook URL endpoint (required)
     * @param  bool  $enabled  Whether the webhook is enabled (default: true)
     * @param  string|null  $name  Webhook name for identification (optional)
     * @param  array  $settings  Additional webhook settings (optional)
     * @param  array  $headers  Custom headers to send with webhook (optional)
     * @param  string|null  $secret  Secret for webhook signature verification (optional)
     * @param  int  $timeout  Webhook timeout in seconds (default: 30)
     * @param  int  $retryCount  Number of retry attempts (default: 3)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $event,
        public readonly string $url,
        public readonly bool $enabled = true,
        public readonly ?string $name = null,
        public readonly array $settings = [],
        public readonly array $headers = [],
        public readonly ?string $secret = null,
        public readonly int $timeout = 30,
        public readonly int $retryCount = 3,
    ) {
        $this->validateEvent($event);
        $this->validateUrl($url);
        $this->validateTimeout($timeout);
        $this->validateRetryCount($retryCount);
        $this->validateHeaders($headers);
        $this->validateSettings($settings);
    }

    /**
     * Create a webhook DTO from an array.
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): static
    {
        return new static(
            event: $data['event'] ?? throw new InvalidArgumentException('Event is required'),
            url: $data['url'] ?? throw new InvalidArgumentException('URL is required'),
            enabled: $data['enabled'] ?? true,
            name: $data['name'] ?? null,
            settings: $data['settings'] ?? [],
            headers: $data['headers'] ?? [],
            secret: $data['secret'] ?? null,
            timeout: $data['timeout'] ?? 30,
            retryCount: $data['retry_count'] ?? 3,
        );
    }

    /**
     * Create a basic webhook with event and URL.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $event, string $url): static
    {
        return new static(event: $event, url: $url);
    }

    /**
     * Create a webhook for subscriber events.
     *
     * @throws InvalidArgumentException
     */
    public static function forSubscriber(string $event, string $url): static
    {
        $validEvents = ['subscriber.created', 'subscriber.updated', 'subscriber.unsubscribed', 'subscriber.bounced', 'subscriber.complained'];

        if (! in_array($event, $validEvents, true)) {
            throw new InvalidArgumentException("Invalid subscriber event '{$event}'. Valid events: ".implode(', ', $validEvents));
        }

        return new static(event: $event, url: $url);
    }

    /**
     * Create a webhook for campaign events.
     *
     * @throws InvalidArgumentException
     */
    public static function forCampaign(string $event, string $url): static
    {
        $validEvents = ['campaign.sent', 'campaign.opened', 'campaign.clicked', 'campaign.bounced', 'campaign.complained', 'campaign.unsubscribed'];

        if (! in_array($event, $validEvents, true)) {
            throw new InvalidArgumentException("Invalid campaign event '{$event}'. Valid events: ".implode(', ', $validEvents));
        }

        return new static(event: $event, url: $url);
    }

    /**
     * Create a webhook for automation events.
     *
     * @throws InvalidArgumentException
     */
    public static function forAutomation(string $event, string $url): static
    {
        $validEvents = ['automation.subscriber_added', 'automation.subscriber_completed', 'automation.email_sent'];

        if (! in_array($event, $validEvents, true)) {
            throw new InvalidArgumentException("Invalid automation event '{$event}'. Valid events: ".implode(', ', $validEvents));
        }

        return new static(event: $event, url: $url);
    }

    /**
     * Create a webhook with custom headers.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithHeaders(string $event, string $url, array $headers): static
    {
        return new static(event: $event, url: $url, headers: $headers);
    }

    /**
     * Create a webhook with a secret for signature verification.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithSecret(string $event, string $url, string $secret): static
    {
        return new static(event: $event, url: $url, secret: $secret);
    }

    /**
     * Convert the DTO to an array for API submission.
     */
    public function toArray(): array
    {
        $data = [
            'events' => [$this->event], // MailerLite API expects 'events' array
            'url' => $this->url,
            'enabled' => $this->enabled,
        ];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if (! empty($this->settings)) {
            $data['settings'] = $this->settings;
        }

        if (! empty($this->headers)) {
            $data['headers'] = $this->headers;
        }

        if ($this->secret !== null) {
            $data['secret'] = $this->secret;
        }

        if ($this->timeout !== 30) {
            $data['timeout'] = $this->timeout;
        }

        if ($this->retryCount !== 3) {
            $data['retry_count'] = $this->retryCount;
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
     * Get a copy with a different event.
     */
    public function withEvent(string $event): static
    {
        return new static(
            event: $event,
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
     * Get a copy with a different URL.
     */
    public function withUrl(string $url): static
    {
        return new static(
            event: $this->event,
            url: $url,
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
     * Get a copy with different enabled status.
     */
    public function withEnabled(bool $enabled): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $enabled,
            name: $this->name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $this->secret,
            timeout: $this->timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Get a copy with a name.
     */
    public function withName(string $name): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $this->secret,
            timeout: $this->timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Get a copy with additional headers.
     */
    public function withHeaders(array $headers): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $this->name,
            settings: $this->settings,
            headers: array_merge($this->headers, $headers),
            secret: $this->secret,
            timeout: $this->timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Get a copy with a secret.
     */
    public function withSecret(string $secret): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $this->name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $secret,
            timeout: $this->timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Get a copy with different timeout.
     */
    public function withTimeout(int $timeout): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $this->name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $this->secret,
            timeout: $timeout,
            retryCount: $this->retryCount,
        );
    }

    /**
     * Get a copy with different retry count.
     */
    public function withRetryCount(int $retryCount): static
    {
        return new static(
            event: $this->event,
            url: $this->url,
            enabled: $this->enabled,
            name: $this->name,
            settings: $this->settings,
            headers: $this->headers,
            secret: $this->secret,
            timeout: $this->timeout,
            retryCount: $retryCount,
        );
    }

    /**
     * Validate webhook event.
     *
     * @throws InvalidArgumentException
     */
    private function validateEvent(string $event): void
    {
        if (empty(trim($event))) {
            throw new InvalidArgumentException('Webhook event cannot be empty.');
        }

        // Define all valid webhook events
        $validEvents = [
            // Subscriber events
            'subscriber.created',
            'subscriber.updated',
            'subscriber.unsubscribed',
            'subscriber.bounced',
            'subscriber.complained',
            'subscriber.deleted',

            // Campaign events
            'campaign.sent',
            'campaign.opened',
            'campaign.clicked',
            'campaign.bounced',
            'campaign.complained',
            'campaign.unsubscribed',
            'campaign.delivered',
            'campaign.soft_bounced',
            'campaign.hard_bounced',

            // Automation events
            'automation.subscriber_added',
            'automation.subscriber_completed',
            'automation.email_sent',
            'automation.started',
            'automation.stopped',

            // Form events
            'form.submitted',

            // Group events
            'group.subscriber_added',
            'group.subscriber_removed',

            // General events
            'webhook.test',
        ];

        if (! in_array($event, $validEvents, true)) {
            throw new InvalidArgumentException(
                "Invalid webhook event '{$event}'. Valid events: ".implode(', ', $validEvents)
            );
        }
    }

    /**
     * Validate webhook URL.
     *
     * @throws InvalidArgumentException
     */
    private function validateUrl(string $url): void
    {
        if (empty(trim($url))) {
            throw new InvalidArgumentException('Webhook URL cannot be empty.');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Invalid webhook URL: {$url}");
        }

        // Ensure URL uses HTTPS for security
        if (! str_starts_with($url, 'https://')) {
            throw new InvalidArgumentException('Webhook URL must use HTTPS for security.');
        }

        // Check URL length
        if (strlen($url) > 2048) {
            throw new InvalidArgumentException('Webhook URL cannot exceed 2048 characters.');
        }
    }

    /**
     * Validate webhook timeout.
     *
     * @throws InvalidArgumentException
     */
    private function validateTimeout(int $timeout): void
    {
        if ($timeout < 1 || $timeout > 300) {
            throw new InvalidArgumentException('Webhook timeout must be between 1 and 300 seconds.');
        }
    }

    /**
     * Validate webhook retry count.
     *
     * @throws InvalidArgumentException
     */
    private function validateRetryCount(int $retryCount): void
    {
        if ($retryCount < 0 || $retryCount > 10) {
            throw new InvalidArgumentException('Webhook retry count must be between 0 and 10.');
        }
    }

    /**
     * Validate webhook headers.
     *
     * @throws InvalidArgumentException
     */
    private function validateHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            if (! is_string($name) || empty(trim($name))) {
                throw new InvalidArgumentException('Header names must be non-empty strings.');
            }

            if (! is_string($value)) {
                throw new InvalidArgumentException("Header '{$name}' value must be a string.");
            }

            // Validate header name format
            if (! preg_match('/^[a-zA-Z0-9-_]+$/', $name)) {
                throw new InvalidArgumentException("Invalid header name '{$name}'. Only alphanumeric characters, hyphens, and underscores are allowed.");
            }
        }
    }

    /**
     * Validate webhook settings.
     *
     * @throws InvalidArgumentException
     */
    private function validateSettings(array $settings): void
    {
        // Validate known settings
        if (isset($settings['verify_ssl']) && ! is_bool($settings['verify_ssl'])) {
            throw new InvalidArgumentException('Setting verify_ssl must be a boolean.');
        }

        if (isset($settings['content_type']) && ! in_array($settings['content_type'], ['application/json', 'application/x-www-form-urlencoded'], true)) {
            throw new InvalidArgumentException('Setting content_type must be either "application/json" or "application/x-www-form-urlencoded".');
        }
    }
}
