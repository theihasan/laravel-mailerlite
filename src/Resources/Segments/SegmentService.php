<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Segments;

use Ihasan\LaravelMailerlite\Contracts\SegmentsInterface;
use Ihasan\LaravelMailerlite\DTOs\SegmentDTO;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Exceptions\SegmentCreateException;
use Ihasan\LaravelMailerlite\Exceptions\SegmentDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\SegmentNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\SegmentUpdateException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Segment Service
 *
 * This service handles all segment-related operations with the MailerLite API.
 * It implements the SegmentsInterface and provides comprehensive error handling
 * and data transformation.
 */
class SegmentService implements SegmentsInterface
{
    /**
     * Create a new segment service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new segment.
     * 
     * Note: MailerLite API does NOT support creating segments programmatically.
     * Segments must be created through the MailerLite web interface.
     *
     * @throws \BadMethodCallException
     */
    public function create(SegmentDTO $segment): array
    {
        throw new \BadMethodCallException(
            'Segment creation is not supported by the MailerLite API. ' .
            'Segments must be created through the MailerLite web interface at https://dashboard.mailerlite.com/. ' .
            'You can then list, update, and delete segments via the API.'
        );
    }

    /**
     * Get a segment by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function get(string $id): ?array
    {
        return $this->getById($id);
    }

    /**
     * Get a segment by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->find($id);

            return $response ? $this->transformSegmentResponse($response) : null;
        } catch (\Exception $e) {
            // If it's a 404, return null instead of throwing
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Update an existing segment.
     *
     * @throws SegmentNotFoundException
     * @throws SegmentUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, SegmentDTO $segment): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->update($id, $segment->toArray());

            return $this->transformSegmentResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete a segment.
     *
     * @throws SegmentNotFoundException
     * @throws SegmentDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->segments->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all segments with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->get($filters);
            
            // The MailerLite SDK wraps the API response in a 'body' key
            $body = $response['body'] ?? $response;

            return [
                'data' => array_map([$this, 'transformSegmentResponse'], $body['data'] ?? []),
                'meta' => $body['meta'] ?? [],
                'links' => $body['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get subscribers in a segment.
     *
     * @throws SegmentNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getSubscribers(string $segmentId, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->getSubscribers($segmentId, $filters);
            
            // The MailerLite SDK wraps the API response in a 'body' key
            $body = $response['body'] ?? $response;

            return [
                'data' => $body['data'] ?? [],
                'meta' => $body['meta'] ?? [],
                'links' => $body['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($segmentId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Refresh/recalculate a segment.
     *
     * @throws SegmentNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function refresh(string $segmentId): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->refresh($segmentId);

            return $this->transformSegmentResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($segmentId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get segment statistics.
     *
     * @throws SegmentNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getStats(string $segmentId): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->stats($segmentId);

            return [
                'subscribers_count' => $response['subscribers_count'] ?? 0,
                'active_count' => $response['active_count'] ?? 0,
                'unsubscribed_count' => $response['unsubscribed_count'] ?? 0,
                'unconfirmed_count' => $response['unconfirmed_count'] ?? 0,
                'bounced_count' => $response['bounced_count'] ?? 0,
                'junk_count' => $response['junk_count'] ?? 0,
                'growth_rate' => $response['growth_rate'] ?? 0.0,
                'last_calculated_at' => $response['last_calculated_at'] ?? null,
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($segmentId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Activate a segment.
     *
     * @throws SegmentNotFoundException
     * @throws SegmentUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function activate(string $segmentId): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->activate($segmentId);

            return $this->transformSegmentResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($segmentId);
            }

            $this->handleUpdateException($segmentId, $e);
        }
    }

    /**
     * Deactivate a segment.
     *
     * @throws SegmentNotFoundException
     * @throws SegmentUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function deactivate(string $segmentId): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->segments->deactivate($segmentId);

            return $this->transformSegmentResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw SegmentNotFoundException::withId($segmentId);
            }

            $this->handleUpdateException($segmentId, $e);
        }
    }

    /**
     * Transform segment response data.
     */
    protected function transformSegmentResponse(array $response): array
    {
        $data = $response['body']['data'] ?? $response;
        
        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'filters' => $data['filters'] ?? [],
            'active' => $data['active'] ?? true,
            'total' => $data['total'] ?? 0, // MailerLite uses 'total' not 'subscribers_count'
            'subscribers_count' => $data['total'] ?? 0, // Alias for backward compatibility
            'open_rate' => $data['open_rate'] ?? ['float' => 0, 'string' => '0%'],
            'click_rate' => $data['click_rate'] ?? ['float' => 0, 'string' => '0%'],
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
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
     * Handle segment creation exceptions.
     *
     * @throws SegmentCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $name, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'already exists') || str_contains($message, 'duplicate')) {
            throw SegmentCreateException::alreadyExists($name);
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw SegmentCreateException::invalidData($name, ['Validation failed']);
        }

        if (str_contains($message, 'filter') || str_contains($message, 'invalid filter')) {
            throw SegmentCreateException::invalidFilters($name, ['Invalid filter configuration']);
        }

        throw SegmentCreateException::make($name, $e->getMessage(), $e);
    }

    /**
     * Handle segment update exceptions.
     *
     * @throws SegmentUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw SegmentUpdateException::invalidData($identifier, ['Validation failed']);
        }

        if (str_contains($message, 'filter') || str_contains($message, 'invalid filter')) {
            throw SegmentUpdateException::invalidFilters($identifier, ['Invalid filter configuration']);
        }

        throw SegmentUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle segment deletion exceptions.
     *
     * @throws SegmentDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        throw SegmentDeleteException::make($identifier, $e->getMessage(), $e);
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
