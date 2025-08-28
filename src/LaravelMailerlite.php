<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite;

use Ihasan\LaravelMailerlite\Contracts\MailerLiteInterface;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldBuilder;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupBuilder;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentBuilder;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberBuilder;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookBuilder;

/**
 * Laravel MailerLite Main Service Class
 *
 * This is the main entry point for the MailerLite package. It provides
 * factory methods for creating builders for different MailerLite resources.
 */
class LaravelMailerlite implements MailerLiteInterface
{
    /**
     * Create a new LaravelMailerlite instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Get a subscriber builder instance.
     */
    public function subscribers(): SubscriberBuilder
    {
        return app(SubscriberBuilder::class);
    }

    /**
     * Get a campaign builder instance.
     */
    public function campaigns(): CampaignBuilder
    {
        return app(CampaignBuilder::class);
    }

    /**
     * Get a group builder instance.
     */
    public function groups(): GroupBuilder
    {
        return app(GroupBuilder::class);
    }

    /**
     * Get a field builder instance.
     */
    public function fields(): FieldBuilder
    {
        return app(FieldBuilder::class);
    }

    /**
     * Get a segment builder instance.
     */
    public function segments(): SegmentBuilder
    {
        return app(SegmentBuilder::class);
    }

    /**
     * Get an automation builder instance.
     */
    public function automations(): AutomationBuilder
    {
        return app(AutomationBuilder::class);
    }

    /**
     * Get a webhook builder instance.
     */
    public function webhooks(): WebhookBuilder
    {
        return app(WebhookBuilder::class);
    }
}
