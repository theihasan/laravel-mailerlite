<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when webhook deletion fails.
 */
class WebhookDeleteException extends MailerLiteException
{
    /**
     * Create a new webhook deletion exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to delete webhook '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'webhook_delete_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for webhook that cannot be deleted.
     */
    public static function cannotDelete(string $identifier, string $status): static
    {
        return static::make($identifier, "Webhook with status '{$status}' cannot be deleted");
    }
}
