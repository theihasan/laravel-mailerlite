<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationBuilder;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationService;

describe('AutomationBuilder', function () {
    beforeEach(function () {
        $this->mockService = mock(AutomationService::class);
        $this->builder = new AutomationBuilder($this->mockService);

        $this->mockResponse = [
            'id' => '123',
            'name' => 'Test Automation',
            'enabled' => true,
            'status' => 'draft',
            'triggers' => [['type' => 'subscriber', 'event' => 'joins_group']],
            'steps' => [['type' => 'email', 'template_id' => 'welcome']]
        ];
    });

    describe('fluent API', function () {
        it('can chain methods fluently', function () {
            $result = $this->builder
                ->create('Test Automation')
                ->description('Test automation description')
                ->enabled()
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delay(1, 'day');

            expect($result)->toBeInstanceOf(AutomationBuilder::class);
        });

        it('sets automation name correctly', function () {
            $this->builder->create('Test Automation');

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->name)->toBe('Test Automation');
        });

        it('sets automation name with named method', function () {
            $this->builder->named('Named Automation');

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->name)->toBe('Named Automation');
        });

        it('sets automation description correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->description('Test description');

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->description)->toBe('Test description');
        });

        it('sets enabled status correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->enabled(false);

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->enabled)->toBeFalse();

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->disabled();

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->enabled)->toBeFalse();
        });

        it('sets automation status correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->status('active');

            $dto = $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->status)->toBe('active');
        });

        it('adds triggers correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->trigger('subscriber', 'joins_group', 'newsletter');

            $dto = $this->builder
                ->sendEmail('welcome-template')
                ->toDTO();

            expect($dto->triggers)->toBe([
                ['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter']
            ]);
        });

        it('adds subscriber triggers with helper methods', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter']
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberSubscribes()
                ->sendEmail('welcome-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'subscriber', 'event' => 'subscribes']
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberUpdatesField('country')
                ->sendEmail('welcome-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'subscriber', 'event' => 'updates_field', 'target' => 'country']
            ]);
        });

        it('adds date triggers correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenDateReached('birthday', 0)
                ->sendEmail('birthday-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'date', 'field' => 'birthday', 'offset' => 0, 'unit' => 'days']
            ]);
        });

        it('adds API and webhook triggers correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenApiCalled('/api/trigger')
                ->sendEmail('api-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'api', 'endpoint' => '/api/trigger']
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenWebhookReceived('https://example.com/webhook')
                ->sendEmail('webhook-template');

            $dto = $this->builder->toDTO();
            expect($dto->triggers)->toBe([
                ['type' => 'webhook', 'endpoint' => 'https://example.com/webhook']
            ]);
        });

        it('adds email steps correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'email', 'template_id' => 'welcome-template']
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendCampaign('welcome-campaign');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'email', 'campaign_id' => 'welcome-campaign']
            ]);
        });

        it('adds delay steps correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delay(1, 'day');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toHaveCount(2);
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 1, 'unit' => 'day']);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delayMinutes(30);

            $dto = $this->builder->toDTO();
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 30, 'unit' => 'minutes']);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delayHours(2);

            $dto = $this->builder->toDTO();
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 2, 'unit' => 'hours']);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delayDays(3);

            $dto = $this->builder->toDTO();
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 3, 'unit' => 'days']);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->delayWeeks(1);

            $dto = $this->builder->toDTO();
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 1, 'unit' => 'weeks']);
        });

        it('adds condition steps correctly', function () {
            $conditions = [['field' => 'country', 'operator' => 'equals', 'value' => 'US']];

            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->condition($conditions);

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'condition', 'conditions' => $conditions]
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->ifField('country', 'equals', 'US');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'condition', 'conditions' => [['field' => 'country', 'operator' => 'equals', 'value' => 'US']]]
            ]);
        });

        it('adds tag action steps correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->addTag('newsletter-subscriber');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'tag', 'action' => 'add', 'tag' => 'newsletter-subscriber']
            ]);

            $this->builder
                ->reset()
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->removeTag('trial-user');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'tag', 'action' => 'remove', 'tag' => 'trial-user']
            ]);
        });

        it('adds field update steps correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->updateField('status', 'subscribed');

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'field_update', 'field' => 'status', 'value' => 'subscribed']
            ]);
        });

        it('adds webhook action steps correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->callWebhook('https://example.com/webhook', ['key' => 'value']);

            $dto = $this->builder->toDTO();
            expect($dto->steps)->toBe([
                ['type' => 'webhook', 'url' => 'https://example.com/webhook', 'data' => ['key' => 'value']]
            ]);
        });

        it('adds settings correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->withSettings(['key1' => 'value1', 'key2' => 'value2'])
                ->withSetting('key3', 'value3')
                ->timezone('America/New_York')
                ->sendTimeBetween('09:00', '17:00')
                ->frequencyCap(5);

            $dto = $this->builder->toDTO();
            expect($dto->settings)->toBe([
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'timezone' => 'America/New_York',
                'send_time' => ['start' => '09:00', 'end' => '17:00'],
                'frequency_cap' => 5
            ]);
        });

        it('adds conditions correctly', function () {
            $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->withConditions([['field' => 'country', 'operator' => 'equals', 'value' => 'US']])
                ->withCondition('age', 'greater_than', 18);

            $dto = $this->builder->toDTO();
            expect($dto->conditions)->toBe([
                ['field' => 'country', 'operator' => 'equals', 'value' => 'US'],
                ['field' => 'age', 'operator' => 'greater_than', 'value' => 18]
            ]);
        });
    });

    describe('automation operations', function () {
        it('saves an automation', function () {
            $this->mockService->shouldReceive('create')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->save();

            expect($result)->toBe($this->mockResponse);
        });

        it('starts an automation', function () {
            $this->mockService->shouldReceive('create')
                ->once()
                ->andReturn($this->mockResponse);

            $this->mockService->shouldReceive('start')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->start();

            expect($result)->toBe($this->mockResponse);
        });

        it('finds an automation by ID', function () {
            $this->mockService->shouldReceive('getById')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->find('123');

            expect($result)->toBe($this->mockResponse);
        });

        it('updates an automation', function () {
            $this->mockService->shouldReceive('update')
                ->with('123', Mockery::type(AutomationDTO::class))
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->create('Updated Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->update('123');

            expect($result)->toBe($this->mockResponse);
        });

        it('deletes an automation', function () {
            $this->mockService->shouldReceive('delete')
                ->with('123')
                ->once()
                ->andReturn(true);

            $result = $this->builder->delete('123');

            expect($result)->toBeTrue();
        });

        it('lists automations', function () {
            $listResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockService->shouldReceive('list')
                ->with([])
                ->once()
                ->andReturn($listResponse);

            $result = $this->builder->list();

            expect($result)->toBe($listResponse);
        });

        it('gets all automations', function () {
            $listResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockService->shouldReceive('list')
                ->with()
                ->once()
                ->andReturn($listResponse);

            $result = $this->builder->all();

            expect($result)->toBe($listResponse);
        });

        it('manages automation state by ID', function () {
            $this->mockService->shouldReceive('start')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->startById('123');
            expect($result)->toBe($this->mockResponse);

            $this->mockService->shouldReceive('stop')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->stopById('123');
            expect($result)->toBe($this->mockResponse);

            $this->mockService->shouldReceive('enable')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->enableById('123');
            expect($result)->toBe($this->mockResponse);

            $this->mockService->shouldReceive('disable')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->disableById('123');
            expect($result)->toBe($this->mockResponse);

            $this->mockService->shouldReceive('pause')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->pauseById('123');
            expect($result)->toBe($this->mockResponse);

            $this->mockService->shouldReceive('resume')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->resumeById('123');
            expect($result)->toBe($this->mockResponse);
        });

        it('gets automation stats', function () {
            $statsResponse = ['subscribers_count' => 100, 'completed_count' => 75];

            $this->mockService->shouldReceive('getStats')
                ->with('123')
                ->once()
                ->andReturn($statsResponse);

            $result = $this->builder->stats('123');

            expect($result)->toBe($statsResponse);
        });

        it('gets automation subscribers', function () {
            $subscribersResponse = [
                'data' => [['id' => '1', 'email' => 'user@example.com']],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockService->shouldReceive('getSubscribers')
                ->with('123', [])
                ->once()
                ->andReturn($subscribersResponse);

            $result = $this->builder->subscribers('123');

            expect($result)->toBe($subscribersResponse);
        });

        it('gets automation activity', function () {
            $activityResponse = [
                'data' => [['id' => '1', 'action' => 'email_sent']],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockService->shouldReceive('getActivity')
                ->with('123', [])
                ->once()
                ->andReturn($activityResponse);

            $result = $this->builder->activity('123');

            expect($result)->toBe($activityResponse);
        });
    });

    describe('validation', function () {
        it('throws exception when creating DTO without name', function () {
            expect(fn () => $this->builder
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template')
                ->toDTO()
            )->toThrow(InvalidArgumentException::class, 'Name is required to create AutomationDTO');
        });
    });

    describe('utility methods', function () {
        it('resets the builder state', function () {
            $this->builder
                ->create('Test Automation')
                ->description('Test description')
                ->whenSubscriberJoinsGroup('newsletter')
                ->sendEmail('welcome-template');

            $this->builder->reset();

            // Use reflection to check private properties
            $reflection = new ReflectionClass($this->builder);

            $nameProperty = $reflection->getProperty('name');
            $nameProperty->setAccessible(true);
            expect($nameProperty->getValue($this->builder))->toBeNull();

            $triggersProperty = $reflection->getProperty('triggers');
            $triggersProperty->setAccessible(true);
            expect($triggersProperty->getValue($this->builder))->toBe([]);

            $stepsProperty = $reflection->getProperty('steps');
            $stepsProperty->setAccessible(true);
            expect($stepsProperty->getValue($this->builder))->toBe([]);
        });

        it('creates a fresh builder instance', function () {
            $fresh = $this->builder->fresh();

            expect($fresh)->toBeInstanceOf(AutomationBuilder::class);
            expect($fresh)->not->toBe($this->builder);
        });
    });

    describe('magic methods', function () {
        it('handles "and" prefixed methods for chaining', function () {
            $result = $this->builder
                ->create('Test Automation')
                ->andDescription('Test description')
                ->andWhenSubscriberJoinsGroup('newsletter')
                ->andSendEmail('welcome-template');

            expect($result)->toBeInstanceOf(AutomationBuilder::class);

            $dto = $result->toDTO();
            expect($dto->name)->toBe('Test Automation');
            expect($dto->description)->toBe('Test description');
            expect($dto->triggers)->toBe([['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter']]);
            expect($dto->steps)->toBe([['type' => 'email', 'template_id' => 'welcome-template']]);
        });

        it('handles "then" prefixed methods for chaining', function () {
            $result = $this->builder
                ->create('Test Automation')
                ->whenSubscriberJoinsGroup('newsletter')
                ->thenSendEmail('welcome-template')
                ->thenDelay(1, 'day');

            expect($result)->toBeInstanceOf(AutomationBuilder::class);

            $dto = $result->toDTO();
            expect($dto->steps)->toHaveCount(2);
            expect($dto->steps[0])->toBe(['type' => 'email', 'template_id' => 'welcome-template']);
            expect($dto->steps[1])->toBe(['type' => 'delay', 'duration' => 1, 'unit' => 'day']);
        });

        it('throws exception for non-existent methods', function () {
            expect(fn () => $this->builder->nonExistentMethod())
                ->toThrow(BadMethodCallException::class);
        });
    });
});
