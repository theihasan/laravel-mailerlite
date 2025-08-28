<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when a webhook is not found.
 */
class WebhookNotFoundException extends MailerLiteException
{
    /**
     * Create a new webhook not found exception.
     */
    public static function withId(string $id): static
    {
        return new static(
            "Webhook with ID '{$id}' was not found.",
            404,
            null,
            ['type' => 'webhook_not_found', 'id' => $id]
        );
    }

    /**
     * Create a new webhook not found exception with custom identifier.
     */
    public static function withIdentifier(string $identifier): static
    {
        return new static(
            "Webhook '{$identifier}' was not found.",
            404,
            null,
            ['type' => 'webhook_not_found', 'identifier' => $identifier]
        );
    }

    /**
     * Create a new webhook not found exception for URL.
     */
    public static function withUrl(string $url): static
    {
        return new static(
            "Webhook with URL '{$url}' was not found.",
            404,
            null,
            ['type' => 'webhook_not_found', 'url' => $url]
        );
    }
}
