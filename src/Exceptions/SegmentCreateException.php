<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

use Throwable;

/**
 * Exception thrown when segment creation fails.
 *
 * This exception is thrown when the MailerLite API returns an error
 * while attempting to create a new segment.
 */
class SegmentCreateException extends MailerLiteException
{
    /**
     * Create a new segment creation exception.
     */
    public function __construct(
        public readonly string $segmentName,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Create an exception for when a segment already exists.
     */
    public static function alreadyExists(string $segmentName): static
    {
        return new static(
            $segmentName,
            "Segment '{$segmentName}' already exists"
        );
    }

    /**
     * Create an exception for invalid segment data.
     */
    public static function invalidData(string $segmentName, array $errors): static
    {
        $errorMessages = implode(', ', $errors);

        return new static(
            $segmentName,
            "Failed to create segment '{$segmentName}': {$errorMessages}"
        );
    }

    /**
     * Create an exception for invalid filters.
     */
    public static function invalidFilters(string $segmentName, array $filterErrors): static
    {
        $errorMessages = implode(', ', $filterErrors);

        return new static(
            $segmentName,
            "Failed to create segment '{$segmentName}' due to invalid filters: {$errorMessages}"
        );
    }

    /**
     * Create a general segment creation exception.
     */
    public static function make(string $segmentName, string $reason, ?Throwable $previous = null): static
    {
        return new static(
            $segmentName,
            "Failed to create segment '{$segmentName}': {$reason}",
            $previous
        );
    }
}
