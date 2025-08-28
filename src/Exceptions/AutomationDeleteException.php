<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when automation deletion fails.
 */
class AutomationDeleteException extends MailerLiteException
{
    /**
     * Create a new automation deletion exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to delete automation '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'automation_delete_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for automation that cannot be deleted.
     */
    public static function cannotDelete(string $identifier, string $status): static
    {
        return static::make($identifier, "Automation with status '{$status}' cannot be deleted");
    }
}
