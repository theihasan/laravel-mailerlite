<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when campaign update fails.
 */
class CampaignUpdateException extends MailerLiteException
{
    /**
     * Create a new campaign update exception.
     */
    public static function make(string $identifier, string $reason, ?\Throwable $previous = null): static
    {
        return new static(
            "Failed to update campaign '{$identifier}': {$reason}",
            422,
            $previous,
            ['type' => 'campaign_update_failed', 'identifier' => $identifier, 'reason' => $reason]
        );
    }

    /**
     * Create exception for invalid campaign data.
     */
    public static function invalidData(string $identifier, array $errors): static
    {
        $reason = 'Invalid data: ' . implode(', ', $errors);

        return static::make($identifier, $reason);
    }

    /**
     * Create exception for campaign that cannot be updated.
     */
    public static function cannotUpdate(string $identifier, string $status): static
    {
        return static::make($identifier, "Campaign with status '{$status}' cannot be updated");
    }
}
