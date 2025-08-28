<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Webhooks;

use Ihasan\LaravelMailerlite\Contracts\WebhooksInterface;
use Ihasan\LaravelMailerlite\DTOs\WebhookDTO;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookCreateException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\WebhookUpdateException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Webhook Service
 *
 * This service handles all webhook-related operations with the MailerLite API.
 * It implements the WebhooksInterface and provides comprehensive error handling
 * and data transformation.
 */
class WebhookService implements WebhooksInterface
{
    /**
     * Create a new webhook service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new webhook.
     *
     * @throws WebhookCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function create(WebhookDTO $webhook): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->create($webhook->toArray());

            return $this->transformWebhookResponse($response);
        } catch (\Exception $e) {
            $this->handleCreateException($webhook->url, $e);
        }
    }

    /**
     * Get a webhook by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->find($id);

            return $response ? $this->transformWebhookResponse($response) : null;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Update an existing webhook.
     *
     * @throws WebhookNotFoundException
     * @throws WebhookUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, WebhookDTO $webhook): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->update($id, $webhook->toArray());

            return $this->transformWebhookResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete a webhook.
     *
     * @throws WebhookNotFoundException
     * @throws WebhookDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->webhooks->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all webhooks with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->get($filters);

            return [
                'data' => array_map([$this, 'transformWebhookResponse'], $response['data'] ?? []),
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Enable a webhook.
     *
     * @throws WebhookNotFoundException
     * @throws WebhookUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function enable(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->update($id, ['enabled' => true]);

            return $this->transformWebhookResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Disable a webhook.
     *
     * @throws WebhookNotFoundException
     * @throws WebhookUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function disable(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->update($id, ['enabled' => false]);

            return $this->transformWebhookResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Test a webhook by sending a test payload.
     *
     * @throws WebhookNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function test(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->test($id);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get webhook delivery logs.
     *
     * @throws WebhookNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getLogs(string $id, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->getLogs($id, $filters);

            return [
                'data' => $response['data'] ?? [],
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get webhook statistics.
     *
     * @throws WebhookNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getStats(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->webhooks->getStats($id);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw WebhookNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Find webhook by URL and event.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function findByUrl(string $url, ?string $event = null): ?array
    {
        try {
            $webhooks = $this->list();
            
            foreach ($webhooks['data'] as $webhook) {
                if ($webhook['url'] === $url) {
                    if ($event === null || $webhook['event'] === $event) {
                        return $webhook;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete webhook by URL and event.
     *
     * @throws WebhookNotFoundException
     * @throws WebhookDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function deleteByUrl(string $url, ?string $event = null): bool
    {
        $webhook = $this->findByUrl($url, $event);
        
        if (! $webhook) {
            throw WebhookNotFoundException::withUrl($url);
        }

        return $this->delete($webhook['id']);
    }

    /**
     * Transform webhook response data.
     */
    protected function transformWebhookResponse(array $response): array
    {
        return [
            'id' => $response['id'] ?? null,
            'account_id' => $response['account_id'] ?? null,
            'event' => $response['event'] ?? null,
            'url' => $response['url'] ?? null,
            'name' => $response['name'] ?? null,
            'enabled' => $response['enabled'] ?? false,
            'secret' => $response['secret'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
            'settings' => $response['settings'] ?? [],
            'headers' => $response['headers'] ?? [],
            'timeout' => $response['timeout'] ?? 30,
            'retry_count' => $response['retry_count'] ?? 3,
            'last_delivery_at' => $response['last_delivery_at'] ?? null,
            'last_delivery_status' => $response['last_delivery_status'] ?? null,
            'delivery_count' => $response['delivery_count'] ?? 0,
            'success_count' => $response['success_count'] ?? 0,
            'failure_count' => $response['failure_count'] ?? 0,
        ];
    }

    /**
     * Check if an exception represents a "not found" error.
     */
    protected function isNotFoundError(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, '404') ||
               str_contains($message, 'not found') ||
               str_contains($message, 'does not exist');
    }

    /**
     * Handle webhook creation exceptions.
     *
     * @throws WebhookCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $url, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw WebhookCreateException::invalidData($url, ['Validation failed']);
        }

        if (str_contains($message, 'already exists') || str_contains($message, 'duplicate')) {
            throw WebhookCreateException::alreadyExists($url);
        }

        if (str_contains($message, 'invalid url') || str_contains($message, 'unreachable')) {
            throw WebhookCreateException::invalidUrl($url);
        }

        if (str_contains($message, 'invalid event')) {
            throw WebhookCreateException::invalidEvent('unknown', $url);
        }

        throw WebhookCreateException::make($url, $e->getMessage(), $e);
    }

    /**
     * Handle webhook update exceptions.
     *
     * @throws WebhookUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw WebhookUpdateException::invalidData($identifier, ['Validation failed']);
        }

        if (str_contains($message, 'cannot be updated')) {
            throw WebhookUpdateException::cannotUpdate($identifier, 'unknown');
        }

        throw WebhookUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle webhook deletion exceptions.
     *
     * @throws WebhookDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'cannot be deleted')) {
            throw WebhookDeleteException::cannotDelete($identifier, 'unknown');
        }

        throw WebhookDeleteException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle general exceptions.
     *
     * @throws MailerLiteAuthenticationException
     */
    protected function handleException(\Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        throw $e;
    }
}
