<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Groups;

use Ihasan\LaravelMailerlite\Contracts\GroupsInterface;
use Ihasan\LaravelMailerlite\DTOs\GroupDTO;
use Ihasan\LaravelMailerlite\Exceptions\GroupCreateException;
use Ihasan\LaravelMailerlite\Exceptions\GroupDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\GroupNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\GroupUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Group Service
 *
 * This service handles all group-related operations with the MailerLite API.
 * It implements the GroupsInterface and provides comprehensive error handling
 * and data transformation.
 */
class GroupService implements GroupsInterface
{
    /**
     * Create a new group service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new group.
     *
     * @throws GroupCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function create(GroupDTO $group): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->create($group->toArray());
            return $this->transformGroupResponse($response);
        } catch (\Exception $e) {
            $this->handleCreateException($group->name, $e);
        }
    }

    /**
     * Get a group by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function get(string $id): ?array
    {
        return $this->getById($id);
    }

    /**
     * Get a group by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->find($id);

            return $response ? $this->transformGroupResponse($response) : null;
        } catch (\Exception $e) {
            // If it's a 404, return null instead of throwing
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Get a group by name.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getByName(string $name): ?array
    {
        try {
            $groups = $this->list();

            foreach ($groups['data'] as $group) {
                if ($group['name'] === $name) {
                    return $group;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update an existing group.
     *
     * @throws GroupNotFoundException
     * @throws GroupUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, GroupDTO $group): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->update($id, $group->toArray());

            return $this->transformGroupResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw GroupNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete a group.
     *
     * @throws GroupNotFoundException
     * @throws GroupDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->groups->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw GroupNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all groups with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->get();
            $body = $response['body'] ?? $response;
            $rawData = $body['data'] ?? [];
            
            $transformedData = [];
            $transformedData = collect($rawData)
                ->map(fn($group) => $this->transformGroupResponse($group))
                ->all();
            
            return [
                'data' => $transformedData,
                'meta' => $body['meta'] ?? [],
                'links' => $body['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get subscribers in a group.
     *
     * @throws GroupNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getSubscribers(string $groupId, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->getSubscribers($groupId, $filters);

            return [
                'data' => $response['data'] ?? [],
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw GroupNotFoundException::withId($groupId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Add subscribers to a group.
     *
     * @throws GroupNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function addSubscribers(string $groupId, array $subscriberIds): array
    {
        return $this->assignSubscribers($groupId, $subscriberIds);
    }

    /**
     * Assign subscribers to a group.
     *
     * @throws GroupNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function assignSubscribers(string $groupId, array $subscriberIds): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->assignSubscriber($groupId, $subscriberIds);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw GroupNotFoundException::withId($groupId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Remove subscribers from a group.
     *
     * @throws GroupNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function removeSubscribers(string $groupId, array $subscriberIds): bool
    {
        $this->unassignSubscribers($groupId, $subscriberIds);

        return true;
    }

    /**
     * Unassign subscribers from a group.
     *
     * @throws GroupNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function unassignSubscribers(string $groupId, array $subscriberIds): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->groups->unassignSubscriber($groupId, $subscriberIds);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw GroupNotFoundException::withId($groupId);
            }

            $this->handleException($e);
        }
    }

    /**
     * Transform group response data.
     */
    protected function transformGroupResponse(array $response): array
    {
        return [
            'id' => $response['body']['data']['id'] ?? null,
            'name' => $response['body']['data']['name'] ?? null,
            'active_count' => $response['body']['data']['active_count'] ?? 0,
            'sent_count' => $response['body']['data']['sent_count'] ?? 0,
            'opens_count' => $response['body']['data']['opens_count'] ?? 0,
            'open_rate' => is_array($response['body']['data']['open_rate'] ?? null) ? ($response['body']['data']['open_rate']['float'] ?? 0) : ($response['body']['data']['open_rate']['string'] ?? 0),
            'clicks_count' => $response['body']['data']['clicks_count'] ?? 0,
            'click_rate' => is_array($response['body']['data']['click_rate'] ?? null) ? ($response['body']['data']['click_rate']['float'] ?? 0) : ($response['body']['data']['click_rate'] ?? 0),
            'unsubscribed_count' => $response['body']['data']['unsubscribed_count'] ?? 0,
            'unconfirmed_count' => $response['body']['data']['unconfirmed_count'] ?? 0,
            'bounced_count' => $response['body']['data']['bounced_count'] ?? 0,
            'junk_count' => $response['body']['data']['junk_count'] ?? 0,
            'created_at' => $response['body']['data']['created_at'] ?? null,
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
     * Handle group creation exceptions.
     *
     * @throws GroupCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $name, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'already exists') || str_contains($message, 'duplicate')) {
            throw GroupCreateException::alreadyExists($name);
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw GroupCreateException::invalidData($name, ['Validation failed']);
        }

        throw GroupCreateException::make($name, $e->getMessage(), $e);
    }

    /**
     * Handle group update exceptions.
     *
     * @throws GroupUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw GroupUpdateException::invalidData($identifier, ['Validation failed']);
        }

        throw GroupUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle group deletion exceptions.
     *
     * @throws GroupDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        throw GroupDeleteException::make($identifier, $e->getMessage(), $e);
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
