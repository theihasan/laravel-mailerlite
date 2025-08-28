<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Facades;

use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldBuilder;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupBuilder;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentBuilder;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberBuilder;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookBuilder;
use Illuminate\Support\Facades\Facade;

/**
 * MailerLite Facade
 *
 * Provides a fluent, plain-English API for interacting with MailerLite services.
 * This facade enables method chaining for readable and expressive code.
 *
 * @method static SubscriberBuilder subscribers()
 * @method static CampaignBuilder campaigns()
 * @method static GroupBuilder groups()
 * @method static FieldBuilder fields()
 * @method static SegmentBuilder segments()
 * @method static AutomationBuilder automations()
 * @method static WebhookBuilder webhooks()
 *
 * @see \Ihasan\LaravelMailerlite\LaravelMailerlite
 */
class MailerLite extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ihasan\LaravelMailerlite\LaravelMailerlite::class;
    }
}
