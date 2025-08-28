<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Contracts;

use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldBuilder;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupBuilder;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentBuilder;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberBuilder;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookBuilder;

/**
 * Main MailerLite package interface.
 *
 * This interface defines the high-level contract for the MailerLite package,
 * providing access to all resource builders for fluent API usage.
 */
interface MailerLiteInterface
{
    /**
     * Get the subscriber builder instance for fluent API operations.
     */
    public function subscribers(): SubscriberBuilder;

    /**
     * Get the campaign builder instance for fluent API operations.
     */
    public function campaigns(): CampaignBuilder;

    /**
     * Get the group builder instance for fluent API operations.
     */
    public function groups(): GroupBuilder;

    /**
     * Get the field builder instance for fluent API operations.
     */
    public function fields(): FieldBuilder;

    /**
     * Get the segment builder instance for fluent API operations.
     */
    public function segments(): SegmentBuilder;

    /**
     * Get the webhook builder instance for fluent API operations.
     */
    public function webhooks(): WebhookBuilder;

    /**
     * Get the automation builder instance for fluent API operations.
     */
    public function automations(): AutomationBuilder;
}
