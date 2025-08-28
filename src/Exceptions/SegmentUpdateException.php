<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Throwable;

/**
 * Exception thrown when segment update fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to update an existing segment.
 */
class SegmentUpdateException extends MailerLiteException
{
    /**
     * Create a new segment update exception.
     */
    public function __construct(
        public readonly string $segmentIdentifier,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for invalid update data.
     */
    public static function invalidData(string $segmentIdentifier, array $errors): static
    {
        $errorMessages = implode(', ', $errors);

        return new static(
            $segmentIdentifier,
            "Failed to update segment '{$segmentIdentifier}': {$errorMessages}"
        );
    }

    /**
     * Create an exception for invalid filters.
     */
    public static function invalidFilters(string $segmentIdentifier, array $filterErrors): static
    {
        $errorMessages = implode(', ', $filterErrors);

        return new static(
            $segmentIdentifier,
            "Failed to update segment '{$segmentIdentifier}' due to invalid filters: {$errorMessages}"
        );
    }

    /**
     * Create a general segment update exception.
     */
    public static function make(string $segmentIdentifier, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $segmentIdentifier,
            "Failed to update segment '{$segmentIdentifier}': {$reason}",
            $previous
        );
    }
}
