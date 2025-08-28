<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Throwable;

/**
 * Exception thrown when field update fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to update an existing custom field.
 */
class FieldUpdateException extends MailerLiteException
{
    /**
     * Create a new field update exception.
     */
    public function __construct(
        public readonly string $fieldIdentifier,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for invalid update data.
     */
    public static function invalidData(string $fieldIdentifier, array $errors): static
    {
        $errorMessages = implode(', ', $errors);

        return new static(
            $fieldIdentifier,
            "Failed to update field '{$fieldIdentifier}': {$errorMessages}"
        );
    }

    /**
     * Create a general field update exception.
     */
    public static function make(string $fieldIdentifier, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $fieldIdentifier,
            "Failed to update field '{$fieldIdentifier}': {$reason}",
            $previous
        );
    }
}
