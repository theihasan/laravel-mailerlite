<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite;

use Ihasan\LaravelMailerlite\Contracts\AutomationsInterface;
use Ihasan\LaravelMailerlite\Contracts\CampaignsInterface;
use Ihasan\LaravelMailerlite\Contracts\FieldsInterface;
use Ihasan\LaravelMailerlite\Contracts\GroupsInterface;
use Ihasan\LaravelMailerlite\Contracts\MailerLiteInterface;
use Ihasan\LaravelMailerlite\Contracts\SegmentsInterface;
use Ihasan\LaravelMailerlite\Contracts\SubscribersInterface;
use Ihasan\LaravelMailerlite\Contracts\WebhooksInterface;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationService;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignService;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldBuilder;
use Ihasan\LaravelMailerlite\Resources\Fields\FieldService;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupBuilder;
use Ihasan\LaravelMailerlite\Resources\Groups\GroupService;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentBuilder;
use Ihasan\LaravelMailerlite\Resources\Segments\SegmentService;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberBuilder;
use Ihasan\LaravelMailerlite\Resources\Subscribers\SubscriberService;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookBuilder;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Laravel MailerLite Service Provider
 *
 * Registers all services, binds contracts to implementations,
 * and configures the package for Laravel integration.
 */
class LaravelMailerliteServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-mailerlite')
            ->hasConfigFile('mailerlite');
    }

    /**
     * Register package services and bindings.
     */
    public function packageRegistered(): void
    {
        // Register the MailerLite Manager as singleton
        $this->app->singleton(MailerLiteManager::class, function ($app) {
            $config = $app['config']['mailerlite'];

            return MailerLiteManager::fromConfig($config);
        });

        // Register services
        $this->app->bind(SubscribersInterface::class, SubscriberService::class);
        $this->app->bind(SubscriberService::class, function ($app) {
            return new SubscriberService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(CampaignsInterface::class, CampaignService::class);
        $this->app->bind(CampaignService::class, function ($app) {
            return new CampaignService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(GroupsInterface::class, GroupService::class);
        $this->app->bind(GroupService::class, function ($app) {
            return new GroupService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(FieldsInterface::class, FieldService::class);
        $this->app->bind(FieldService::class, function ($app) {
            return new FieldService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(SegmentsInterface::class, SegmentService::class);
        $this->app->bind(SegmentService::class, function ($app) {
            return new SegmentService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(AutomationsInterface::class, AutomationService::class);
        $this->app->bind(AutomationService::class, function ($app) {
            return new AutomationService($app->make(MailerLiteManager::class));
        });

        $this->app->bind(WebhooksInterface::class, WebhookService::class);
        $this->app->bind(WebhookService::class, function ($app) {
            return new WebhookService($app->make(MailerLiteManager::class));
        });

        // Register builders
        $this->app->bind(SubscriberBuilder::class, function ($app) {
            return new SubscriberBuilder($app->make(SubscriberService::class));
        });

        $this->app->bind(CampaignBuilder::class, function ($app) {
            return new CampaignBuilder($app->make(CampaignService::class));
        });

        $this->app->bind(GroupBuilder::class, function ($app) {
            return new GroupBuilder($app->make(GroupService::class));
        });

        $this->app->bind(FieldBuilder::class, function ($app) {
            return new FieldBuilder($app->make(FieldService::class));
        });

        $this->app->bind(SegmentBuilder::class, function ($app) {
            return new SegmentBuilder($app->make(SegmentService::class));
        });

        $this->app->bind(AutomationBuilder::class, function ($app) {
            return new AutomationBuilder($app->make(AutomationService::class));
        });

        $this->app->bind(WebhookBuilder::class, function ($app) {
            return new WebhookBuilder($app->make(WebhookService::class));
        });

        // Register main service
        $this->app->singleton(LaravelMailerlite::class, function ($app) {
            return new LaravelMailerlite($app->make(MailerLiteManager::class));
        });

        // Bind main interface
        $this->app->bind(MailerLiteInterface::class, LaravelMailerlite::class);
    }

    /**
     * Boot package services.
     */
    public function packageBooted(): void
    {
        // Any additional booting logic can go here
    }
}
