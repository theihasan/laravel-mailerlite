<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when webhook creation fails.
 */
class WebhookCreateException extends MailerLiteException
{
    /**
     * Create a new webhook creation exception.
     */
    public static function make(string $url, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to create webhook for URL '{$url}': {$reason}",
            422,
            $previous,
            ['type' => 'webhook_create_failed', 'url' => $url, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid webhook data.
     */
    public static function invalidData(string $url, array $errors): static
    {
        $reason = 'Invalid data: ' . implode(', ', $errors);

        return static::make($url, $reason);
    }

    /**
     * Create exception for invalid webhook URL.
     */
    public static function invalidUrl(string $url): static
    {
        return static::make($url, 'Invalid or unreachable webhook URL');
    }

    /**
     * Create exception for invalid webhook event.
     */
    public static function invalidEvent(string $event, string $url): static
    {
        return static::make($url, "Invalid webhook event '{$event}'");
    }

    /**
     * Create exception for webhook that already exists.
     */
    public static function alreadyExists(string $url): static
    {
        return static::make($url, 'Webhook already exists for this URL and event');
    }
}
