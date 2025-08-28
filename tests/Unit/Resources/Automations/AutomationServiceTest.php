<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;
use Ihasan\LaravelMailerlite\Exceptions\AutomationCreateException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationStateException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Automations\AutomationService;
use MailerLite\MailerLite;

describe('AutomationService', function () {
    beforeEach(function () {
        $this->mockClient = mock(MailerLite::class);
        $this->mockManager = mock(MailerLiteManager::class);
        $this->mockManager->shouldReceive('getClient')->andReturn($this->mockClient);

        $this->service = new AutomationService($this->mockManager);

        $this->automationDTO = new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'welcome']]
        );

        $this->mockResponse = [
            'id' => '123',
            'name' => 'Test Automation',
            'enabled' => true,
            'status' => 'draft',
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 12:00:00',
            'triggers' => [['type' => 'subscriber', 'event' => 'joins_group']],
            'steps' => [['type' => 'email', 'template_id' => 'welcome']],
        ];
    });

    describe('create', function () {
        it('creates an automation successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->with($this->automationDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->create($this->automationDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('name', 'Test Automation');
        });

        it('handles authentication errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->create($this->automationDTO))
                ->toThrow(MailerLiteAuthenticationException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->create($this->automationDTO))
                ->toThrow(AutomationCreateException::class);
        });

        it('handles invalid triggers', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->andThrow(new Exception('Invalid trigger configuration'));

            expect(fn () => $this->service->create($this->automationDTO))
                ->toThrow(AutomationCreateException::class);
        });

        it('handles invalid steps', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->andThrow(new Exception('Invalid step configuration'));

            expect(fn () => $this->service->create($this->automationDTO))
                ->toThrow(AutomationCreateException::class);
        });

        it('handles general creation errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('create')
                ->andThrow(new Exception('Something went wrong'));

            expect(fn () => $this->service->create($this->automationDTO))
                ->toThrow(AutomationCreateException::class);
        });
    });

    describe('getById', function () {
        it('gets an automation by ID successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('find')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->getById('123');

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('name', 'Test Automation');
        });

        it('returns null when automation not found', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('find')
                ->with('123')
                ->andThrow(new Exception('404 Not found'));

            $result = $this->service->getById('123');

            expect($result)->toBeNull();
        });

        it('handles authentication errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('find')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->getById('123'))
                ->toThrow(MailerLiteAuthenticationException::class);
        });
    });

    describe('update', function () {
        it('updates an automation successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('update')
                ->with('123', $this->automationDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->update('123', $this->automationDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('name', 'Test Automation');
        });

        it('throws not found exception when automation does not exist', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('update')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->update('123', $this->automationDTO))
                ->toThrow(AutomationNotFoundException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('update')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->update('123', $this->automationDTO))
                ->toThrow(AutomationUpdateException::class);
        });

        it('handles cannot update errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('update')
                ->andThrow(new Exception('Automation cannot be updated'));

            expect(fn () => $this->service->update('123', $this->automationDTO))
                ->toThrow(AutomationUpdateException::class);
        });
    });

    describe('delete', function () {
        it('deletes an automation successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('delete')
                ->with('123')
                ->andReturn(null);

            $result = $this->service->delete('123');

            expect($result)->toBeTrue();
        });

        it('throws not found exception when automation does not exist', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('delete')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(AutomationNotFoundException::class);
        });

        it('handles cannot delete errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('delete')
                ->andThrow(new Exception('Automation cannot be deleted'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(AutomationDeleteException::class);
        });
    });

    describe('list', function () {
        it('lists automations successfully', function () {
            $mockResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => [],
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('get')
                ->with([])
                ->andReturn($mockResponse);

            $result = $this->service->list();

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(1);
            expect($result['data'][0])->toHaveKey('id', '123');
        });

        it('lists automations with filters', function () {
            $filters = ['status' => 'active'];
            $mockResponse = [
                'data' => [],
                'meta' => ['total' => 0],
                'links' => [],
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('get')
                ->with($filters)
                ->andReturn($mockResponse);

            $result = $this->service->list($filters);

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(0);
        });
    });

    describe('start', function () {
        it('starts an automation successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('start')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->start('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when automation does not exist', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('start')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->start('123'))
                ->toThrow(AutomationNotFoundException::class);
        });

        it('handles cannot start errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('start')
                ->andThrow(new Exception('Automation cannot be started'));

            expect(fn () => $this->service->start('123'))
                ->toThrow(AutomationStateException::class);
        });

        it('handles already started errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('start')
                ->andThrow(new Exception('Automation is already active'));

            expect(fn () => $this->service->start('123'))
                ->toThrow(AutomationStateException::class);
        });
    });

    describe('stop', function () {
        it('stops an automation successfully', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('stop')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->stop('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when automation does not exist', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('stop')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->stop('123'))
                ->toThrow(AutomationNotFoundException::class);
        });

        it('handles cannot stop errors', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('stop')
                ->andThrow(new Exception('Automation cannot be stopped'));

            expect(fn () => $this->service->stop('123'))
                ->toThrow(AutomationStateException::class);
        });
    });

    describe('getSubscribers', function () {
        it('gets automation subscribers successfully', function () {
            $subscribersResponse = [
                'data' => [
                    ['id' => '1', 'email' => 'user1@example.com'],
                    ['id' => '2', 'email' => 'user2@example.com'],
                ],
                'meta' => ['total' => 2],
                'links' => [],
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('getSubscribers')
                ->with('123', [])
                ->andReturn($subscribersResponse);

            $result = $this->service->getSubscribers('123');

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(2);
        });

        it('gets automation subscribers with filters', function () {
            $filters = ['status' => 'active'];
            $subscribersResponse = [
                'data' => [['id' => '1', 'email' => 'user1@example.com']],
                'meta' => ['total' => 1],
                'links' => [],
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('getSubscribers')
                ->with('123', $filters)
                ->andReturn($subscribersResponse);

            $result = $this->service->getSubscribers('123', $filters);

            expect($result['data'])->toHaveCount(1);
        });
    });

    describe('getActivity', function () {
        it('gets automation activity successfully', function () {
            $activityResponse = [
                'data' => [
                    ['id' => '1', 'action' => 'email_sent', 'created_at' => '2024-01-01 12:00:00'],
                    ['id' => '2', 'action' => 'delay_completed', 'created_at' => '2024-01-01 13:00:00'],
                ],
                'meta' => ['total' => 2],
                'links' => [],
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('getActivity')
                ->with('123', [])
                ->andReturn($activityResponse);

            $result = $this->service->getActivity('123');

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(2);
        });
    });

    describe('getStats', function () {
        it('gets automation stats successfully', function () {
            $statsResponse = [
                'subscribers_count' => 100,
                'completed_count' => 75,
                'active_count' => 25,
            ];

            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('getStats')
                ->with('123')
                ->andReturn($statsResponse);

            $result = $this->service->getStats('123');

            expect($result)->toBe($statsResponse);
        });

        it('throws not found exception when automation does not exist', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('getStats')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->getStats('123'))
                ->toThrow(AutomationNotFoundException::class);
        });
    });

    describe('state management methods', function () {
        it('enables automation (alias for start)', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('start')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->enable('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('disables automation (alias for stop)', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('stop')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->disable('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('pauses automation', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('pause')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->pause('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('resumes automation', function () {
            $this->mockClient->automations = mock();
            $this->mockClient->automations->shouldReceive('resume')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->resume('123');

            expect($result)->toHaveKey('id', '123');
        });
    });

    describe('transformAutomationResponse', function () {
        it('transforms automation response correctly', function () {
            $response = [
                'id' => '123',
                'name' => 'Test Automation',
                'enabled' => true,
                'status' => 'draft',
                'created_at' => '2024-01-01 12:00:00',
            ];

            // Use reflection to test the protected method
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('transformAutomationResponse');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, $response);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('name', 'Test Automation');
            expect($result)->toHaveKey('enabled', true);
            expect($result)->toHaveKey('status', 'draft');
            expect($result)->toHaveKey('created_at', '2024-01-01 12:00:00');
            expect($result)->toHaveKey('subscribers_count', 0);
            expect($result)->toHaveKey('completed_count', 0);
            expect($result)->toHaveKey('active_count', 0);
        });
    });
});
