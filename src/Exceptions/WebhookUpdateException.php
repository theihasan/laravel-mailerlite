<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when webhook update fails.
 */
class WebhookUpdateException extends MailerLiteException
{
    /**
     * Create a new webhook update exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to update webhook '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'webhook_update_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid webhook data.
     */
    public static function invalidData(string $identifier, array $errors): static
    {
        $reason = 'Invalid data: '.implode(', ', $errors);

        return static::make($identifier, $reason);
    }

    /**
     * Create exception for webhook that cannot be updated.
     */
    public static function cannotUpdate(string $identifier, string $status): static
    {
        return static::make($identifier, "Webhook with status '{$status}' cannot be updated");
    }
}
