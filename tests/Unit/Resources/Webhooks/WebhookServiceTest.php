<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\WebhookDTO;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookCreateException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookUpdateException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Webhooks\WebhookService;
use MailerLite\MailerLite;

describe('WebhookService', function () {
    beforeEach(function () {
        $this->mockClient = mock(MailerLite::class);
        $this->mockManager = mock(MailerLiteManager::class);
        $this->mockManager->shouldReceive('getClient')->andReturn($this->mockClient);

        $this->service = new WebhookService($this->mockManager);

        $this->webhookDTO = new WebhookDTO(
            event: 'subscriber.created',
            url: 'https://example.com/webhook'
        );

        $this->mockResponse = [
            'id' => '123',
            'event' => 'subscriber.created',
            'url' => 'https://example.com/webhook',
            'enabled' => true,
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 12:00:00'
        ];
    });

    describe('create', function () {
        it('creates a webhook successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->with($this->webhookDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->create($this->webhookDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('event', 'subscriber.created');
            expect($result)->toHaveKey('url', 'https://example.com/webhook');
        });

        it('handles authentication errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(MailerLiteAuthenticationException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(WebhookCreateException::class);
        });

        it('handles already exists errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('Webhook already exists'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(WebhookCreateException::class);
        });

        it('handles invalid URL errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('Invalid URL or unreachable'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(WebhookCreateException::class);
        });

        it('handles invalid event errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('Invalid event'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(WebhookCreateException::class);
        });

        it('handles general creation errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('create')
                ->andThrow(new Exception('Something went wrong'));

            expect(fn () => $this->service->create($this->webhookDTO))
                ->toThrow(WebhookCreateException::class);
        });
    });

    describe('getById', function () {
        it('gets a webhook by ID successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('find')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->getById('123');

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('event', 'subscriber.created');
        });

        it('returns null when webhook not found', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('find')
                ->with('123')
                ->andThrow(new Exception('404 Not found'));

            $result = $this->service->getById('123');

            expect($result)->toBeNull();
        });

        it('handles authentication errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('find')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->getById('123'))
                ->toThrow(MailerLiteAuthenticationException::class);
        });
    });

    describe('update', function () {
        it('updates a webhook successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->with('123', $this->webhookDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->update('123', $this->webhookDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('event', 'subscriber.created');
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->update('123', $this->webhookDTO))
                ->toThrow(WebhookNotFoundException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->update('123', $this->webhookDTO))
                ->toThrow(WebhookUpdateException::class);
        });

        it('handles cannot update errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->andThrow(new Exception('Webhook cannot be updated'));

            expect(fn () => $this->service->update('123', $this->webhookDTO))
                ->toThrow(WebhookUpdateException::class);
        });
    });

    describe('delete', function () {
        it('deletes a webhook successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('delete')
                ->with('123')
                ->andReturn(null);

            $result = $this->service->delete('123');

            expect($result)->toBeTrue();
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('delete')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(WebhookNotFoundException::class);
        });

        it('handles cannot delete errors', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('delete')
                ->andThrow(new Exception('Webhook cannot be deleted'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(WebhookDeleteException::class);
        });
    });

    describe('list', function () {
        it('lists webhooks successfully', function () {
            $mockResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($mockResponse);

            $result = $this->service->list();

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(1);
            expect($result['data'][0])->toHaveKey('id', '123');
        });

        it('lists webhooks with filters', function () {
            $filters = ['event' => 'subscriber.created'];
            $mockResponse = [
                'data' => [],
                'meta' => ['total' => 0],
                'links' => []
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with($filters)
                ->andReturn($mockResponse);

            $result = $this->service->list($filters);

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(0);
        });
    });

    describe('enable', function () {
        it('enables a webhook successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->with('123', ['enabled' => true])
                ->andReturn($this->mockResponse);

            $result = $this->service->enable('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->enable('123'))
                ->toThrow(WebhookNotFoundException::class);
        });
    });

    describe('disable', function () {
        it('disables a webhook successfully', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->with('123', ['enabled' => false])
                ->andReturn($this->mockResponse);

            $result = $this->service->disable('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('update')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->disable('123'))
                ->toThrow(WebhookNotFoundException::class);
        });
    });

    describe('test', function () {
        it('tests a webhook successfully', function () {
            $testResponse = ['message' => 'Test payload sent'];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('test')
                ->with('123')
                ->andReturn($testResponse);

            $result = $this->service->test('123');

            expect($result)->toBe($testResponse);
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('test')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->test('123'))
                ->toThrow(WebhookNotFoundException::class);
        });
    });

    describe('getLogs', function () {
        it('gets webhook logs successfully', function () {
            $logsResponse = [
                'data' => [
                    ['id' => '1', 'status' => 'success', 'created_at' => '2024-01-01 12:00:00'],
                    ['id' => '2', 'status' => 'failed', 'created_at' => '2024-01-01 11:00:00']
                ],
                'meta' => ['total' => 2],
                'links' => []
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('getLogs')
                ->with('123', [])
                ->andReturn($logsResponse);

            $result = $this->service->getLogs('123');

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(2);
        });

        it('gets webhook logs with filters', function () {
            $filters = ['status' => 'failed'];
            $logsResponse = [
                'data' => [['id' => '2', 'status' => 'failed']],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('getLogs')
                ->with('123', $filters)
                ->andReturn($logsResponse);

            $result = $this->service->getLogs('123', $filters);

            expect($result['data'])->toHaveCount(1);
        });
    });

    describe('getStats', function () {
        it('gets webhook stats successfully', function () {
            $statsResponse = [
                'delivery_count' => 100,
                'success_count' => 95,
                'failure_count' => 5
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('getStats')
                ->with('123')
                ->andReturn($statsResponse);

            $result = $this->service->getStats('123');

            expect($result)->toBe($statsResponse);
        });

        it('throws not found exception when webhook does not exist', function () {
            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('getStats')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->getStats('123'))
                ->toThrow(WebhookNotFoundException::class);
        });
    });

    describe('findByUrl', function () {
        it('finds webhook by URL successfully', function () {
            $listResponse = [
                'data' => [
                    [
                        'id' => '123',
                        'event' => 'subscriber.created',
                        'url' => 'https://example.com/webhook'
                    ],
                    [
                        'id' => '456',
                        'event' => 'subscriber.updated',
                        'url' => 'https://other.com/webhook'
                    ]
                ]
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($listResponse);

            $result = $this->service->findByUrl('https://example.com/webhook');

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('url', 'https://example.com/webhook');
        });

        it('finds webhook by URL and event successfully', function () {
            $listResponse = [
                'data' => [
                    [
                        'id' => '123',
                        'event' => 'subscriber.created',
                        'url' => 'https://example.com/webhook'
                    ],
                    [
                        'id' => '456',
                        'event' => 'subscriber.updated',
                        'url' => 'https://example.com/webhook'
                    ]
                ]
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($listResponse);

            $result = $this->service->findByUrl('https://example.com/webhook', 'subscriber.updated');

            expect($result)->toHaveKey('id', '456');
            expect($result)->toHaveKey('event', 'subscriber.updated');
        });

        it('returns null when webhook not found by URL', function () {
            $listResponse = [
                'data' => [
                    [
                        'id' => '123',
                        'event' => 'subscriber.created',
                        'url' => 'https://other.com/webhook'
                    ]
                ]
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($listResponse);

            $result = $this->service->findByUrl('https://example.com/webhook');

            expect($result)->toBeNull();
        });
    });

    describe('deleteByUrl', function () {
        it('deletes webhook by URL successfully', function () {
            $listResponse = [
                'data' => [
                    [
                        'id' => '123',
                        'event' => 'subscriber.created',
                        'url' => 'https://example.com/webhook'
                    ]
                ]
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($listResponse);

            $this->mockClient->webhooks->shouldReceive('delete')
                ->with('123')
                ->andReturn(null);

            $result = $this->service->deleteByUrl('https://example.com/webhook');

            expect($result)->toBeTrue();
        });

        it('throws not found exception when webhook not found by URL', function () {
            $listResponse = [
                'data' => []
            ];

            $this->mockClient->webhooks = mock();
            $this->mockClient->webhooks->shouldReceive('get')
                ->with([])
                ->andReturn($listResponse);

            expect(fn () => $this->service->deleteByUrl('https://example.com/webhook'))
                ->toThrow(WebhookNotFoundException::class);
        });
    });

    describe('transformWebhookResponse', function () {
        it('transforms webhook response correctly', function () {
            $response = [
                'id' => '123',
                'event' => 'subscriber.created',
                'url' => 'https://example.com/webhook',
                'enabled' => true,
                'created_at' => '2024-01-01 12:00:00'
            ];

            // Use reflection to test the protected method
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('transformWebhookResponse');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, $response);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('event', 'subscriber.created');
            expect($result)->toHaveKey('url', 'https://example.com/webhook');
            expect($result)->toHaveKey('enabled', true);
            expect($result)->toHaveKey('created_at', '2024-01-01 12:00:00');
            expect($result)->toHaveKey('delivery_count', 0);
            expect($result)->toHaveKey('success_count', 0);
            expect($result)->toHaveKey('failure_count', 0);
        });
    });
});
