<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\WebhookDTO;

describe('WebhookDTO', function () {
    it('can be created with required fields', function () {
        $dto = new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook'
        );

        expect($dto->event)->toBe('subscriber.created');
        expect($dto->url)->toBe('https://example.com/webhook');
        expect($dto->enabled)->toBeTrue();
        expect($dto->name)->toBeNull();
        expect($dto->settings)->toBe([]);
        expect($dto->headers)->toBe([]);
        expect($dto->secret)->toBeNull();
        expect($dto->timeout)->toBe(30);
        expect($dto->retryCount)->toBe(3);
    });

    it('can be created with all fields', function () {
        $settings = ['verify_ssl' => true];
        $headers = ['Authorization' => 'Bearer token'];

        $dto = new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            enabled: false,
            name: 'Test Webhook',
            settings: $settings,
            headers: $headers,
            secret: 'secret-key',
            timeout: 60,
            retryCount: 5
        );

        expect($dto->event)->toBe('subscriber.created');
        expect($dto->url)->toBe('https://example.com/webhook');
        expect($dto->enabled)->toBeFalse();
        expect($dto->name)->toBe('Test Webhook');
        expect($dto->settings)->toBe($settings);
        expect($dto->headers)->toBe($headers);
        expect($dto->secret)->toBe('secret-key');
        expect($dto->timeout)->toBe(60);
        expect($dto->retryCount)->toBe(5);
    });

    it('validates event is not empty', function () {
        expect(fn () => new WebhookDTO(
            event: '',
            url: 'https://example.com/webhook'
        ))->toThrow(InvalidArgumentException::class, 'Webhook event cannot be empty.');
    });

    it('validates event is valid', function () {
        expect(fn () => new WebhookDTO(
            event: 'invalid.event',
            url: 'https://example.com/webhook'
        ))->toThrow(InvalidArgumentException::class, "Invalid webhook event 'invalid.event'");
    });

    it('validates URL is not empty', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: ''
        ))->toThrow(InvalidArgumentException::class, 'Webhook URL cannot be empty.');
    });

    it('validates URL format', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'invalid-url'
        ))->toThrow(InvalidArgumentException::class, 'Invalid webhook URL: invalid-url');
    });

    it('validates URL uses HTTPS', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'http://example.com/webhook'
        ))->toThrow(InvalidArgumentException::class, 'Webhook URL must use HTTPS for security.');
    });

    it('validates URL length', function () {
        $longUrl = 'https://example.com/'.str_repeat('a', 2048);

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: $longUrl
        ))->toThrow(InvalidArgumentException::class, 'Webhook URL cannot exceed 2048 characters.');
    });

    it('validates timeout range', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            timeout: 0
        ))->toThrow(InvalidArgumentException::class, 'Webhook timeout must be between 1 and 300 seconds.');

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            timeout: 301
        ))->toThrow(InvalidArgumentException::class, 'Webhook timeout must be between 1 and 300 seconds.');
    });

    it('validates retry count range', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            retryCount: -1
        ))->toThrow(InvalidArgumentException::class, 'Webhook retry count must be between 0 and 10.');

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            retryCount: 11
        ))->toThrow(InvalidArgumentException::class, 'Webhook retry count must be between 0 and 10.');
    });

    it('validates header names and values', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            headers: ['' => 'value']
        ))->toThrow(InvalidArgumentException::class, 'Header names must be non-empty strings.');

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            headers: ['name' => 123]
        ))->toThrow(InvalidArgumentException::class, "Header 'name' value must be a string.");

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            headers: ['invalid@name' => 'value']
        ))->toThrow(InvalidArgumentException::class, "Invalid header name 'invalid@name'. Only alphanumeric characters, hyphens, and underscores are allowed.");
    });

    it('validates settings', function () {
        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            settings: ['verify_ssl' => 'not-boolean']
        ))->toThrow(InvalidArgumentException::class, 'Setting verify_ssl must be a boolean.');

        expect(fn () => new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            settings: ['content_type' => 'invalid']
        ))->toThrow(InvalidArgumentException::class, 'Setting content_type must be either "application/json" or "application/x-www-form-urlencoded".');
    });

    it('accepts all valid events', function () {
        $validEvents = [
            'subscriber.created',
            'subscriber.updated',
            'subscriber.unsubscribed',
            'subscriber.bounced',
            'subscriber.complained',
            'subscriber.deleted',
            'campaign.sent',
            'campaign.opened',
            'campaign.clicked',
            'campaign.bounced',
            'campaign.complained',
            'campaign.unsubscribed',
            'campaign.delivered',
            'campaign.soft_bounced',
            'campaign.hard_bounced',
            'automation.subscriber_added',
            'automation.subscriber_completed',
            'automation.email_sent',
            'automation.started',
            'automation.stopped',
            'form.submitted',
            'group.subscriber_added',
            'group.subscriber_removed',
            'webhook.test',
        ];

        foreach ($validEvents as $event) {
            $dto = new WebhookDTO(
                event: $event,
                url: 'https://example.com/webhook'
            );
            expect($dto->event)->toBe($event);
        }
    });

    it('can be created from array', function () {
        $data = [
            'event' => 'subscriber.created',
            'url' => 'https://example.com/webhook',
            'enabled' => false,
            'name' => 'Test Webhook',
            'settings' => ['verify_ssl' => true],
            'headers' => ['Authorization' => 'Bearer token'],
            'secret' => 'secret-key',
            'timeout' => 60,
            'retry_count' => 5,
        ];

        $dto = WebhookDTO::fromArray($data);

        expect($dto->event)->toBe('subscriber.created');
        expect($dto->url)->toBe('https://example.com/webhook');
        expect($dto->enabled)->toBeFalse();
        expect($dto->name)->toBe('Test Webhook');
        expect($dto->settings)->toBe(['verify_ssl' => true]);
        expect($dto->headers)->toBe(['Authorization' => 'Bearer token']);
        expect($dto->secret)->toBe('secret-key');
        expect($dto->timeout)->toBe(60);
        expect($dto->retryCount)->toBe(5);
    });

    it('can convert to array', function () {
        $dto = new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook',
            enabled: false,
            name: 'Test Webhook',
            settings: ['verify_ssl' => true],
            headers: ['Authorization' => 'Bearer token'],
            secret: 'secret-key',
            timeout: 60,
            retryCount: 5
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'event' => 'subscriber.created',
            'url' => 'https://example.com/webhook',
            'enabled' => false,
            'name' => 'Test Webhook',
            'settings' => ['verify_ssl' => true],
            'headers' => ['Authorization' => 'Bearer token'],
            'secret' => 'secret-key',
            'timeout' => 60,
            'retry_count' => 5,
        ]);
    });

    it('has factory methods', function () {
        $dto = WebhookDTO::create('subscriber.created', 'https://example.com/webhook');
        expect($dto->event)->toBe('subscriber.created');
        expect($dto->url)->toBe('https://example.com/webhook');

        $dto = WebhookDTO::forSubscriber('subscriber.created', 'https://example.com/webhook');
        expect($dto->event)->toBe('subscriber.created');
        expect($dto->url)->toBe('https://example.com/webhook');

        $dto = WebhookDTO::forCampaign('campaign.sent', 'https://example.com/webhook');
        expect($dto->event)->toBe('campaign.sent');
        expect($dto->url)->toBe('https://example.com/webhook');

        $dto = WebhookDTO::forAutomation('automation.email_sent', 'https://example.com/webhook');
        expect($dto->event)->toBe('automation.email_sent');
        expect($dto->url)->toBe('https://example.com/webhook');

        $dto = WebhookDTO::withHeaders('subscriber.created', 'https://example.com/webhook', ['Auth' => 'token']);
        expect($dto->headers)->toBe(['Auth' => 'token']);

        $dto = WebhookDTO::withSecret('subscriber.created', 'https://example.com/webhook', 'secret');
        expect($dto->secret)->toBe('secret');
    });

    it('validates factory method events', function () {
        expect(fn () => WebhookDTO::forSubscriber('invalid', 'https://example.com/webhook'))
            ->toThrow(InvalidArgumentException::class, "Invalid subscriber event 'invalid'");

        expect(fn () => WebhookDTO::forCampaign('invalid', 'https://example.com/webhook'))
            ->toThrow(InvalidArgumentException::class, "Invalid campaign event 'invalid'");

        expect(fn () => WebhookDTO::forAutomation('invalid', 'https://example.com/webhook'))
            ->toThrow(InvalidArgumentException::class, "Invalid automation event 'invalid'");
    });

    it('has immutable with methods', function () {
        $original = new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook'
        );

        $withEvent = $original->withEvent('subscriber.updated');
        expect($original->event)->toBe('subscriber.created');
        expect($withEvent->event)->toBe('subscriber.updated');

        $withUrl = $original->withUrl('https://newdomain.com/webhook');
        expect($original->url)->toBe('https://example.com/webhook');
        expect($withUrl->url)->toBe('https://newdomain.com/webhook');

        $withEnabled = $original->withEnabled(false);
        expect($original->enabled)->toBeTrue();
        expect($withEnabled->enabled)->toBeFalse();

        $withName = $original->withName('New Name');
        expect($original->name)->toBeNull();
        expect($withName->name)->toBe('New Name');

        $withHeaders = $original->withHeaders(['Auth' => 'token']);
        expect($original->headers)->toBe([]);
        expect($withHeaders->headers)->toBe(['Auth' => 'token']);

        $withSecret = $original->withSecret('secret');
        expect($original->secret)->toBeNull();
        expect($withSecret->secret)->toBe('secret');

        $withTimeout = $original->withTimeout(60);
        expect($original->timeout)->toBe(30);
        expect($withTimeout->timeout)->toBe(60);

        $withRetryCount = $original->withRetryCount(5);
        expect($original->retryCount)->toBe(3);
        expect($withRetryCount->retryCount)->toBe(5);
    });
});
