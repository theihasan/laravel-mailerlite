<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when campaign sending fails.
 */
class CampaignSendException extends MailerLiteException
{
    /**
     * Create a new campaign send exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to send campaign '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'campaign_send_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for campaign that cannot be sent.
     */
    public static function cannotSend(string $identifier, string $status): static
    {
        return static::make($identifier, "Campaign with status '{$status}' cannot be sent");
    }

    /**
     * Create exception for campaign with no recipients.
     */
    public static function noRecipients(string $identifier): static
    {
        return static::make($identifier, 'Campaign has no recipients to send to');
    }
}
