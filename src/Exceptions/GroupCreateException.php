<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when group creation fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to create a new group.
 */
class GroupCreateException extends MailerLiteException
{
    /**
     * Create a new group creation exception.
     */
    public function __construct(
        public readonly string $groupName,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for when a group already exists.
     */
    public static function alreadyExists(string $groupName): static
    {
        return new static(
            $groupName,
            "Group '{$groupName}' already exists"
        );
    }

    /**
     * Create an exception for invalid group data.
     */
    public static function invalidData(string $groupName, array $errors): static
    {
        $errorMessages = implode(', ', $errors);

        return new static(
            $groupName,
            "Failed to create group '{$groupName}': {$errorMessages}"
        );
    }

    /**
     * Create a general group creation exception.
     */
    public static function make(string $groupName, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $groupName,
            "Failed to create group '{$groupName}': {$reason}",
            $previous
        );
    }
}
