<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when automation update fails.
 */
class AutomationUpdateException extends MailerLiteException
{
    /**
     * Create a new automation update exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to update automation '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'automation_update_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid automation data.
     */
    public static function invalidData(string $identifier, array $errors): static
    {
        $reason = 'Invalid data: '.implode(', ', $errors);

        return static::make($identifier, $reason);
    }

    /**
     * Create exception for automation that cannot be updated.
     */
    public static function cannotUpdate(string $identifier, string $status): static
    {
        return static::make($identifier, "Automation with status '{$status}' cannot be updated");
    }
}
