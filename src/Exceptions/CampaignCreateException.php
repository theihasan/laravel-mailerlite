<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when campaign creation fails.
 */
class CampaignCreateException extends MailerLiteException
{
    /**
     * Create a new campaign creation exception.
     */
    public static function make(string $subject, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to create campaign with subject '{$subject}': {$reason}",
            422,
            $previous,
            ['type' => 'campaign_create_failed', 'subject' => $subject, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid campaign data.
     */
    public static function invalidData(string $subject, array $errors): static
    {
        $reason = 'Invalid data: ' . implode(', ', $errors);

        return static::make($subject, $reason);
    }

    /**
     * Create exception for missing required fields.
     */
    public static function missingRequiredFields(string $subject, array $fields): static
    {
        $reason = 'Missing required fields: ' . implode(', ', $fields);

        return static::make($subject, $reason);
    }

    /**
     * Create exception for invalid recipients (no groups or segments).
     */
    public static function noRecipients(string $subject): static
    {
        return static::make($subject, 'Campaign must have at least one group or segment');
    }
}
