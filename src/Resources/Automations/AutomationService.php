<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Automations;

use Ihasan\LaravelMailerlite\Contracts\AutomationsInterface;
use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;
use Ihasan\LaravelMailerlite\Exceptions\AutomationCreateException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationStateException;
use Ihasan\LaravelMailerlite\Exceptions\AutomationUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Automation Service
 *
 * This service handles all automation-related operations with the MailerLite API.
 * It implements the AutomationsInterface and provides comprehensive error handling
 * and data transformation.
 */
class AutomationService implements AutomationsInterface
{
    /**
     * Create a new automation service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new automation.
     *
     * @throws AutomationCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function create(AutomationDTO $automation): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->create($automation->toArray());

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            $this->handleCreateException($automation->name, $e);
        }
    }

    /**
     * Get an automation by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->find($id);

            return $response ? $this->transformAutomationResponse($response) : null;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Update an existing automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, AutomationDTO $automation): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->update($id, $automation->toArray());

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete an automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->automations->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all automations with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->get($filters);

            return [
                'data' => array_map([$this, 'transformAutomationResponse'], $response['data'] ?? []),
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Start/enable an automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function start(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->start($id);

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleStateException($id, 'start', $e);
        }
    }

    /**
     * Stop/disable an automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function stop(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->stop($id);

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleStateException($id, 'stop', $e);
        }
    }

    /**
     * Get subscribers in an automation.
     *
     * @throws AutomationNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getSubscribers(string $id, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->getSubscribers($id, $filters);

            return [
                'data' => $response['data'] ?? [],
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get automation activity/stats.
     *
     * @throws AutomationNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getActivity(string $id, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->getActivity($id, $filters);

            return [
                'data' => $response['data'] ?? [],
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get automation statistics.
     *
     * @throws AutomationNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getStats(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->getStats($id);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Enable an automation (alias for start).
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function enable(string $id): array
    {
        return $this->start($id);
    }

    /**
     * Disable an automation (alias for stop).
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function disable(string $id): array
    {
        return $this->stop($id);
    }

    /**
     * Pause an automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function pause(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->pause($id);

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleStateException($id, 'pause', $e);
        }
    }

    /**
     * Resume a paused automation.
     *
     * @throws AutomationNotFoundException
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    public function resume(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->automations->resume($id);

            return $this->transformAutomationResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw AutomationNotFoundException::withId($id);
            }

            $this->handleStateException($id, 'resume', $e);
        }
    }

    /**
     * Transform automation response data.
     */
    protected function transformAutomationResponse(array $response): array
    {
        return [
            'id' => $response['id'] ?? null,
            'account_id' => $response['account_id'] ?? null,
            'name' => $response['name'] ?? null,
            'description' => $response['description'] ?? null,
            'enabled' => $response['enabled'] ?? false,
            'status' => $response['status'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
            'triggered_at' => $response['triggered_at'] ?? null,
            'completed_at' => $response['completed_at'] ?? null,
            'triggers' => $response['triggers'] ?? [],
            'steps' => $response['steps'] ?? [],
            'settings' => $response['settings'] ?? [],
            'conditions' => $response['conditions'] ?? [],
            'stats' => $response['stats'] ?? [],
            'subscribers_count' => $response['subscribers_count'] ?? 0,
            'completed_count' => $response['completed_count'] ?? 0,
            'active_count' => $response['active_count'] ?? 0,
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
     * Handle automation creation exceptions.
     *
     * @throws AutomationCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $name, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw AutomationCreateException::invalidData($name, ['Validation failed']);
        }

        if (str_contains($message, 'trigger') || str_contains($message, 'invalid trigger')) {
            throw AutomationCreateException::invalidTriggers($name);
        }

        if (str_contains($message, 'step') || str_contains($message, 'action') || str_contains($message, 'invalid step')) {
            throw AutomationCreateException::invalidSteps($name);
        }

        throw AutomationCreateException::make($name, $e->getMessage(), $e);
    }

    /**
     * Handle automation update exceptions.
     *
     * @throws AutomationUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw AutomationUpdateException::invalidData($identifier, ['Validation failed']);
        }

        if (str_contains($message, 'cannot be updated') || str_contains($message, 'active')) {
            throw AutomationUpdateException::cannotUpdate($identifier, 'active');
        }

        throw AutomationUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle automation deletion exceptions.
     *
     * @throws AutomationDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'cannot be deleted') || str_contains($message, 'active')) {
            throw AutomationDeleteException::cannotDelete($identifier, 'active');
        }

        throw AutomationDeleteException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle automation state change exceptions.
     *
     * @throws AutomationStateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleStateException(string $identifier, string $action, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'cannot be started')) {
            throw AutomationStateException::cannotStart($identifier, 'unknown');
        }

        if (str_contains($message, 'cannot be stopped')) {
            throw AutomationStateException::cannotStop($identifier, 'unknown');
        }

        if (str_contains($message, 'already')) {
            throw AutomationStateException::alreadyInState($identifier, $action);
        }

        throw AutomationStateException::make($identifier, $action, $e->getMessage(), $e);
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
