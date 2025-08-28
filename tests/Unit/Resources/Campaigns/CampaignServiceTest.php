<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;
use Ihasan\LaravelMailerlite\Exceptions\CampaignCreateException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignSendException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;
use Ihasan\LaravelMailerlite\Resources\Campaigns\CampaignService;
use MailerLite\MailerLite;

describe('CampaignService', function () {
    beforeEach(function () {
        $this->mockClient = mock(MailerLite::class);
        $this->mockManager = mock(MailerLiteManager::class);
        $this->mockManager->shouldReceive('getClient')->andReturn($this->mockClient);

        $this->service = new CampaignService($this->mockManager);

        $this->campaignDTO = new CampaignDTO(
            subject: 'Test Campaign',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        );

        $this->mockResponse = [
            'id' => '123',
            'subject' => 'Test Campaign',
            'from_name' => 'Test Sender',
            'from_email' => 'sender@example.com',
            'status' => 'draft',
            'type' => 'regular',
            'created_at' => '2024-01-01 12:00:00',
            'updated_at' => '2024-01-01 12:00:00'
        ];
    });

    describe('create', function () {
        it('creates a campaign successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('create')
                ->with($this->campaignDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->create($this->campaignDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('subject', 'Test Campaign');
        });

        it('handles authentication errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('create')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->create($this->campaignDTO))
                ->toThrow(MailerLiteAuthenticationException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('create')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->create($this->campaignDTO))
                ->toThrow(CampaignCreateException::class);
        });

        it('handles general creation errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('create')
                ->andThrow(new Exception('Something went wrong'));

            expect(fn () => $this->service->create($this->campaignDTO))
                ->toThrow(CampaignCreateException::class);
        });
    });

    describe('getById', function () {
        it('gets a campaign by ID successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('find')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->getById('123');

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('subject', 'Test Campaign');
        });

        it('returns null when campaign not found', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('find')
                ->with('123')
                ->andThrow(new Exception('404 Not found'));

            $result = $this->service->getById('123');

            expect($result)->toBeNull();
        });

        it('handles authentication errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('find')
                ->andThrow(new Exception('401 Unauthorized'));

            expect(fn () => $this->service->getById('123'))
                ->toThrow(MailerLiteAuthenticationException::class);
        });
    });

    describe('update', function () {
        it('updates a campaign successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('update')
                ->with('123', $this->campaignDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->update('123', $this->campaignDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('subject', 'Test Campaign');
        });

        it('throws not found exception when campaign does not exist', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('update')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->update('123', $this->campaignDTO))
                ->toThrow(CampaignNotFoundException::class);
        });

        it('handles validation errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('update')
                ->andThrow(new Exception('422 Validation failed'));

            expect(fn () => $this->service->update('123', $this->campaignDTO))
                ->toThrow(CampaignUpdateException::class);
        });

        it('handles cannot update errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('update')
                ->andThrow(new Exception('Campaign cannot be updated'));

            expect(fn () => $this->service->update('123', $this->campaignDTO))
                ->toThrow(CampaignUpdateException::class);
        });
    });

    describe('delete', function () {
        it('deletes a campaign successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('delete')
                ->with('123')
                ->andReturn(null);

            $result = $this->service->delete('123');

            expect($result)->toBeTrue();
        });

        it('throws not found exception when campaign does not exist', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('delete')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(CampaignNotFoundException::class);
        });

        it('handles cannot delete errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('delete')
                ->andThrow(new Exception('Campaign cannot be deleted'));

            expect(fn () => $this->service->delete('123'))
                ->toThrow(CampaignDeleteException::class);
        });
    });

    describe('list', function () {
        it('lists campaigns successfully', function () {
            $mockResponse = [
                'data' => [$this->mockResponse],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('get')
                ->with([])
                ->andReturn($mockResponse);

            $result = $this->service->list();

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(1);
            expect($result['data'][0])->toHaveKey('id', '123');
        });

        it('lists campaigns with filters', function () {
            $filters = ['status' => 'sent'];
            $mockResponse = [
                'data' => [],
                'meta' => ['total' => 0],
                'links' => []
            ];

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('get')
                ->with($filters)
                ->andReturn($mockResponse);

            $result = $this->service->list($filters);

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(0);
        });
    });

    describe('schedule', function () {
        it('schedules a campaign successfully', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('schedule')
                ->with('123', ['schedule_at' => $scheduleAt->format('Y-m-d H:i:s')])
                ->andReturn($this->mockResponse);

            $result = $this->service->schedule('123', $scheduleAt);

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when campaign does not exist', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('schedule')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->schedule('123', $scheduleAt))
                ->toThrow(CampaignNotFoundException::class);
        });

        it('handles send errors', function () {
            $scheduleAt = new DateTime('+1 hour');

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('schedule')
                ->andThrow(new Exception('Cannot schedule campaign'));

            expect(fn () => $this->service->schedule('123', $scheduleAt))
                ->toThrow(CampaignSendException::class);
        });
    });

    describe('send', function () {
        it('sends a campaign successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('send')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->send('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when campaign does not exist', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('send')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->send('123'))
                ->toThrow(CampaignNotFoundException::class);
        });

        it('handles no recipients errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('send')
                ->andThrow(new Exception('No recipients to send to'));

            expect(fn () => $this->service->send('123'))
                ->toThrow(CampaignSendException::class);
        });

        it('handles cannot send errors', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('send')
                ->andThrow(new Exception('Campaign cannot be sent'));

            expect(fn () => $this->service->send('123'))
                ->toThrow(CampaignSendException::class);
        });
    });

    describe('cancel', function () {
        it('cancels a campaign successfully', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('cancel')
                ->with('123')
                ->andReturn($this->mockResponse);

            $result = $this->service->cancel('123');

            expect($result)->toHaveKey('id', '123');
        });

        it('throws not found exception when campaign does not exist', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('cancel')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->cancel('123'))
                ->toThrow(CampaignNotFoundException::class);
        });
    });

    describe('getStats', function () {
        it('gets campaign stats successfully', function () {
            $statsResponse = [
                'sent' => 100,
                'opened' => 50,
                'clicked' => 10
            ];

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('getStats')
                ->with('123')
                ->andReturn($statsResponse);

            $result = $this->service->getStats('123');

            expect($result)->toBe($statsResponse);
        });

        it('throws not found exception when campaign does not exist', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('getStats')
                ->andThrow(new Exception('404 Not found'));

            expect(fn () => $this->service->getStats('123'))
                ->toThrow(CampaignNotFoundException::class);
        });
    });

    describe('getSubscribers', function () {
        it('gets campaign subscribers successfully', function () {
            $subscribersResponse = [
                'data' => [
                    ['id' => '1', 'email' => 'user1@example.com'],
                    ['id' => '2', 'email' => 'user2@example.com']
                ],
                'meta' => ['total' => 2],
                'links' => []
            ];

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('getSubscribers')
                ->with('123', [])
                ->andReturn($subscribersResponse);

            $result = $this->service->getSubscribers('123');

            expect($result)->toHaveKey('data');
            expect($result['data'])->toHaveCount(2);
        });

        it('gets campaign subscribers with filters', function () {
            $filters = ['status' => 'opened'];
            $subscribersResponse = [
                'data' => [['id' => '1', 'email' => 'user1@example.com']],
                'meta' => ['total' => 1],
                'links' => []
            ];

            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('getSubscribers')
                ->with('123', $filters)
                ->andReturn($subscribersResponse);

            $result = $this->service->getSubscribers('123', $filters);

            expect($result['data'])->toHaveCount(1);
        });
    });

    describe('draft', function () {
        it('creates a draft campaign (alias for create)', function () {
            $this->mockClient->campaigns = mock();
            $this->mockClient->campaigns->shouldReceive('create')
                ->with($this->campaignDTO->toArray())
                ->andReturn($this->mockResponse);

            $result = $this->service->draft($this->campaignDTO);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('subject', 'Test Campaign');
        });
    });

    describe('transformCampaignResponse', function () {
        it('transforms campaign response correctly', function () {
            $response = [
                'id' => '123',
                'subject' => 'Test Campaign',
                'from_name' => 'Test Sender',
                'status' => 'draft',
                'created_at' => '2024-01-01 12:00:00'
            ];

            // Use reflection to test the protected method
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('transformCampaignResponse');
            $method->setAccessible(true);

            $result = $method->invoke($this->service, $response);

            expect($result)->toHaveKey('id', '123');
            expect($result)->toHaveKey('subject', 'Test Campaign');
            expect($result)->toHaveKey('from_name', 'Test Sender');
            expect($result)->toHaveKey('status', 'draft');
            expect($result)->toHaveKey('created_at', '2024-01-01 12:00:00');
        });
    });
});
