<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when automation state management fails.
 */
class AutomationStateException extends MailerLiteException
{
    /**
     * Create a new automation state exception.
     */
    public static function make(string $identifier, string $action, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to {$action} automation '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'automation_state_failed', 'identifier' => $identifier, 'action' => $action, 'reason' => $reason]
        );
    }

    /**
     * Create exception for automation that cannot be started.
     */
    public static function cannotStart(string $identifier, string $currentStatus): static
    {
        return static::make($identifier, 'start', "Automation with status '{$currentStatus}' cannot be started");
    }

    /**
     * Create exception for automation that cannot be stopped.
     */
    public static function cannotStop(string $identifier, string $currentStatus): static
    {
        return static::make($identifier, 'stop', "Automation with status '{$currentStatus}' cannot be stopped");
    }

    /**
     * Create exception for automation that is already in the desired state.
     */
    public static function alreadyInState(string $identifier, string $state): static
    {
        return static::make($identifier, 'change state', "Automation is already {$state}");
    }
}
