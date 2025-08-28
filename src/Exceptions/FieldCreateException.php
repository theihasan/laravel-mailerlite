<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Throwable;

/**
 * Exception thrown when field creation fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to create a new custom field.
 */
class FieldCreateException extends MailerLiteException
{
    /**
     * Create a new field creation exception.
     */
    public function __construct(
        public readonly string $fieldName,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for when a field already exists.
     */
    public static function alreadyExists(string $fieldName): static
    {
        return new static(
            $fieldName,
            "Field '{$fieldName}' already exists"
        );
    }

    /**
     * Create an exception for invalid field data.
     */
    public static function invalidData(string $fieldName, array $errors): static
    {
        $errorMessages = implode(', ', $errors);

        return new static(
            $fieldName,
            "Failed to create field '{$fieldName}': {$errorMessages}"
        );
    }

    /**
     * Create a general field creation exception.
     */
    public static function make(string $fieldName, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $fieldName,
            "Failed to create field '{$fieldName}': {$reason}",
            $previous
        );
    }
}
