<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignBuilder;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignService;

describe('CampaignBuilder', function () {
    beforeEach(function () {
        $this->mockService = mock(CampaignService::class);
        $this->builder = new CampaignBuilder($this->mockService);

        $this->mockResponse = [
            'id' => '123',
            'subject' => 'Test Campaign',
            'from_name' => 'Test Sender',
            'from_email' => 'sender@example.com',
            'status' => 'draft',
            'type' => 'regular',
        ];
    });

    describe('fluent API', function () {
        it('can chain methods fluently', function () {
            $result = $this->builder
                ->draft()
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Hello World</h1>')
                ->toGroup('newsletter')
                ->toSegment('active-users');

            expect($result)->toBeInstanceOf(CampaignBuilder::class);
        });

        it('sets subject correctly', function () {
            $this->builder->subject('Test Campaign');

            $dto = $this->builder
                ->fromName('Test Sender')
                ->fromEmail('sender@example.com')
                ->html('<h1>Test</h1>')
                ->toDTO();

            expect($dto->subject)->toBe('Test Campaign');
        });

        it('sets from information correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>');

            $dto = $this->builder->toDTO();

            expect($dto->fromName)->toBe('Test Sender');
            expect($dto->fromEmail)->toBe('sender@example.com');
        });

        it('sets from name and email separately', function () {
            $this->builder
                ->subject('Test')
                ->fromName('Test Sender')
                ->fromEmail('sender@example.com')
                ->html('<h1>Test</h1>');

            $dto = $this->builder->toDTO();

            expect($dto->fromName)->toBe('Test Sender');
            expect($dto->fromEmail)->toBe('sender@example.com');
        });

        it('sets HTML content correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Hello World</h1>');

            $dto = $this->builder->toDTO();

            expect($dto->html)->toBe('<h1>Hello World</h1>');
        });

        it('sets plain content correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->plain('Hello World');

            $dto = $this->builder->toDTO();

            expect($dto->plain)->toBe('Hello World');
        });

        it('sets both HTML and plain content', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->content('<h1>Hello World</h1>', 'Hello World');

            $dto = $this->builder->toDTO();

            expect($dto->html)->toBe('<h1>Hello World</h1>');
            expect($dto->plain)->toBe('Hello World');
        });

        it('adds single group correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toGroup('newsletter');

            $dto = $this->builder->toDTO();

            expect($dto->groups)->toBe(['newsletter']);
        });

        it('adds multiple groups correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toGroups(['newsletter', 'promotions']);

            $dto = $this->builder->toDTO();

            expect($dto->groups)->toBe(['newsletter', 'promotions']);
        });

        it('adds single segment correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toSegment('active-users');

            $dto = $this->builder->toDTO();

            expect($dto->segments)->toBe(['active-users']);
        });

        it('adds multiple segments correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toSegments(['active-users', 'premium-users']);

            $dto = $this->builder->toDTO();

            expect($dto->segments)->toBe(['active-users', 'premium-users']);
        });

        it('sets schedule time correctly', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->scheduleAt($scheduleAt);

            $dto = $this->builder->toDTO();

            expect($dto->scheduleAt)->toBe($scheduleAt);
        });

        it('schedules in minutes correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->scheduleIn(60);

            $dto = $this->builder->toDTO();

            expect($dto->scheduleAt)->toBeInstanceOf(DateTime::class);
        });

        it('schedules for specific date correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->scheduleFor('2024-12-31 23:59:59');

            $dto = $this->builder->toDTO();

            expect($dto->scheduleAt)->toBeInstanceOf(DateTime::class);
        });

        it('sets campaign type correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->regular();

            $dto = $this->builder->toDTO();
            expect($dto->type)->toBe('regular');

            $this->builder
                ->reset()
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->abTest(['test_type' => 'subject', 'send_size' => 25]);

            $dto = $this->builder->toDTO();
            expect($dto->type)->toBe('ab');
            expect($dto->abSettings)->toBe(['test_type' => 'subject', 'send_size' => 25]);

            $this->builder
                ->reset()
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->resend();

            $dto = $this->builder->toDTO();
            expect($dto->type)->toBe('resend');
        });

        it('sets campaign settings correctly', function () {
            $this->builder
                ->subject('Test')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->withSettings(['key1' => 'value1', 'key2' => 'value2'])
                ->withSetting('key3', 'value3');

            $dto = $this->builder->toDTO();

            expect($dto->settings)->toBe([
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
            ]);
        });
    });

    describe('campaign operations', function () {
        it('creates a campaign', function () {
            $this->mockService->shouldReceive('create')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->create();

            expect($result)->toBe($this->mockResponse);
        });

        it('sends a campaign immediately', function () {
            $this->mockService->shouldReceive('create')
                ->once()
                ->andReturn($this->mockResponse);

            $this->mockService->shouldReceive('send')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->send();

            expect($result)->toBe($this->mockResponse);
        });

        it('schedules a campaign', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->mockService->shouldReceive('create')
                ->once()
                ->andReturn($this->mockResponse);

            $this->mockService->shouldReceive('schedule')
                ->with('123', $scheduleAt)
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->scheduleAt($scheduleAt)
                ->schedule();

            expect($result)->toBe($this->mockResponse);
        });

        it('throws exception when scheduling without schedule time', function () {
            expect(fn () => $this->builder
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->schedule()
            )->toThrow(InvalidArgumentException::class, 'Schedule time is required to schedule campaign');
        });

        it('finds a campaign by ID', function () {
            $this->mockService->shouldReceive('getById')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->find('123');

            expect($result)->toBe($this->mockResponse);
        });

        it('updates a campaign', function () {
            $this->mockService->shouldReceive('update')
                ->with('123', Mockery::type(CampaignDTO::class))
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder
                ->subject('Updated Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Updated</h1>')
                ->update('123');

            expect($result)->toBe($this->mockResponse);
        });

        it('deletes a campaign', function () {
            $this->mockService->shouldReceive('delete')
                ->with('123')
                ->once()
                ->andReturn(true);

            $result = $this->builder->delete('123');

            expect($result)->toBeTrue();
        });

        it('lists campaigns', function () {
            $listResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => [],
            ];

            $this->mockService->shouldReceive('list')
                ->with([])
                ->once()
                ->andReturn($listResponse);

            $result = $this->builder->list();

            expect($result)->toBe($listResponse);
        });

        it('gets all campaigns', function () {
            $listResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => [],
            ];

            $this->mockService->shouldReceive('list')
                ->with()
                ->once()
                ->andReturn($listResponse);

            $result = $this->builder->all();

            expect($result)->toBe($listResponse);
        });

        it('gets campaign stats', function () {
            $statsResponse = ['sent' => 100, 'opened' => 50];

            $this->mockService->shouldReceive('getStats')
                ->with('123')
                ->once()
                ->andReturn($statsResponse);

            $result = $this->builder->stats('123');

            expect($result)->toBe($statsResponse);
        });

        it('gets campaign subscribers', function () {
            $subscribersResponse = [
                'data' => [['id' => '1', 'email' => 'user@example.com']],
                'meta' => ['total' => 1],
                'links' => [],
            ];

            $this->mockService->shouldReceive('getSubscribers')
                ->with('123', [])
                ->once()
                ->andReturn($subscribersResponse);

            $result = $this->builder->subscribers('123');

            expect($result)->toBe($subscribersResponse);
        });

        it('sends campaign by ID', function () {
            $this->mockService->shouldReceive('send')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->sendById('123');

            expect($result)->toBe($this->mockResponse);
        });

        it('schedules campaign by ID', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->mockService->shouldReceive('schedule')
                ->with('123', $scheduleAt)
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->scheduleById('123', $scheduleAt);

            expect($result)->toBe($this->mockResponse);
        });

        it('cancels a campaign', function () {
            $this->mockService->shouldReceive('cancel')
                ->with('123')
                ->once()
                ->andReturn($this->mockResponse);

            $result = $this->builder->cancel('123');

            expect($result)->toBe($this->mockResponse);
        });
    });

    describe('validation', function () {
        it('throws exception when creating DTO without subject', function () {
            expect(fn () => $this->builder
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toDTO()
            )->toThrow(InvalidArgumentException::class, 'Subject is required to create CampaignDTO');
        });

        it('throws exception when creating DTO without from name', function () {
            expect(fn () => $this->builder
                ->subject('Test Campaign')
                ->fromEmail('sender@example.com')
                ->html('<h1>Test</h1>')
                ->toDTO()
            )->toThrow(InvalidArgumentException::class, 'From name is required to create CampaignDTO');
        });

        it('throws exception when creating DTO without from email', function () {
            expect(fn () => $this->builder
                ->subject('Test Campaign')
                ->fromName('Test Sender')
                ->html('<h1>Test</h1>')
                ->toDTO()
            )->toThrow(InvalidArgumentException::class, 'From email is required to create CampaignDTO');
        });
    });

    describe('utility methods', function () {
        it('resets the builder state', function () {
            $this->builder
                ->subject('Test Campaign')
                ->from('Test Sender', 'sender@example.com')
                ->html('<h1>Test</h1>')
                ->toGroup('newsletter');

            $this->builder->reset();

            // Use reflection to check private properties
            $reflection = new ReflectionClass($this->builder);

            $subjectProperty = $reflection->getProperty('subject');
            $subjectProperty->setAccessible(true);
            expect($subjectProperty->getValue($this->builder))->toBeNull();

            $groupsProperty = $reflection->getProperty('groups');
            $groupsProperty->setAccessible(true);
            expect($groupsProperty->getValue($this->builder))->toBe([]);
        });

        it('creates a fresh builder instance', function () {
            $fresh = $this->builder->fresh();

            expect($fresh)->toBeInstanceOf(CampaignBuilder::class);
            expect($fresh)->not->toBe($this->builder);
        });
    });

    describe('magic methods', function () {
        it('handles "and" prefixed methods for chaining', function () {
            $result = $this->builder
                ->subject('Test Campaign')
                ->andFrom('Test Sender', 'sender@example.com')
                ->andHtml('<h1>Test</h1>')
                ->andToGroup('newsletter');

            expect($result)->toBeInstanceOf(CampaignBuilder::class);

            $dto = $result->toDTO();
            expect($dto->subject)->toBe('Test Campaign');
            expect($dto->fromName)->toBe('Test Sender');
            expect($dto->fromEmail)->toBe('sender@example.com');
            expect($dto->html)->toBe('<h1>Test</h1>');
            expect($dto->groups)->toBe(['newsletter']);
        });

        it('throws exception for non-existent methods', function () {
            expect(fn () => $this->builder->nonExistentMethod())
                ->toThrow(BadMethodCallException::class);
        });
    });
});
