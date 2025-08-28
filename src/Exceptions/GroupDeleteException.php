<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Throwable;

/**
 * Exception thrown when group deletion fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to delete a group.
 */
class GroupDeleteException extends MailerLiteException
{
    /**
     * Create a new group deletion exception.
     */
    public function __construct(
        public readonly string $groupIdentifier,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create a general group deletion exception.
     */
    public static function make(string $groupIdentifier, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $groupIdentifier,
            "Failed to delete group '{$groupIdentifier}': {$reason}",
            $previous
        );
    }
}
