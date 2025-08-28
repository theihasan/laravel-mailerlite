<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when automation creation fails.
 */
class AutomationCreateException extends MailerLiteException
{
    /**
     * Create a new automation creation exception.
     */
    public static function make(string $name, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to create automation '{$name}': {$reason}",
            422,
            $previous,
            ['type' => 'automation_create_failed', 'name' => $name, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid automation data.
     */
    public static function invalidData(string $name, array $errors): static
    {
        $reason = 'Invalid data: ' . implode(', ', $errors);

        return static::make($name, $reason);
    }

    /**
     * Create exception for missing required fields.
     */
    public static function missingRequiredFields(string $name, array $fields): static
    {
        $reason = 'Missing required fields: ' . implode(', ', $fields);

        return static::make($name, $reason);
    }

    /**
     * Create exception for invalid triggers.
     */
    public static function invalidTriggers(string $name): static
    {
        return static::make($name, 'Automation must have valid triggers');
    }

    /**
     * Create exception for invalid steps.
     */
    public static function invalidSteps(string $name): static
    {
        return static::make($name, 'Automation must have valid steps/actions');
    }
}
