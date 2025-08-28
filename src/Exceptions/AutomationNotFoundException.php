<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when an automation is not found.
 */
class AutomationNotFoundException extends MailerLiteException
{
    /**
     * Create a new automation not found exception.
     */
    public static function withId(string $id): static
    {
        return new static(
            "Automation with ID '{$id}' was not found.",
            404,
            null,
            ['type' => 'automation_not_found', 'id' => $id]
        );
    }

    /**
     * Create a new automation not found exception with custom identifier.
     */
    public static function withIdentifier(string $identifier): static
    {
        return new static(
            "Automation '{$identifier}' was not found.",
            404,
            null,
            ['type' => 'automation_not_found', 'identifier' => $identifier]
        );
    }
}
