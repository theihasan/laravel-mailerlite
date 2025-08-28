<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Exceptions;

/**
 * Exception thrown when a campaign is not found.
 */
class CampaignNotFoundException extends MailerLiteException
{
    /**
     * Create a new campaign not found exception.
     */
    public static function withId(string $id): static
    {
        return new static(
            "Campaign with ID '{$id}' was not found.",
            404,
            null,
            ['type' => 'campaign_not_found', 'id' => $id]
        );
    }

    /**
     * Create a new campaign not found exception with custom identifier.
     */
    public static function withIdentifier(string $identifier): static
    {
        return new static(
            "Campaign '{$identifier}' was not found.",
            404,
            null,
            ['type' => 'campaign_not_found', 'identifier' => $identifier]
        );
    }
}
