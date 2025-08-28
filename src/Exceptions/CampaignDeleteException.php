<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when campaign deletion fails.
 */
class CampaignDeleteException extends MailerLiteException
{
    /**
     * Create a new campaign deletion exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to delete campaign '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'campaign_delete_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for campaign that cannot be deleted.
     */
    public static function cannotDelete(string $identifier, string $status): static
    {
        return static::make($identifier, "Campaign with status '{$status}' cannot be deleted");
    }
}
