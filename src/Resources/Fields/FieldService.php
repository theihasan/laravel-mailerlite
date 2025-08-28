<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Fields;

use Ihasan\LaravelMailerlite\Contracts\FieldsInterface;
use Ihasan\LaravelMailerlite\DTOs\FieldDTO;
use Ihasan\LaravelMailerlite\Exceptions\FieldCreateException;
use Ihasan\LaravelMailerlite\Exceptions\FieldDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\FieldNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\FieldUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Field Service
 *
 * This service handles all custom field-related operations with the MailerLite API.
 * It implements the FieldsInterface and provides comprehensive error handling
 * and data transformation.
 */
class FieldService implements FieldsInterface
{
    /**
     * Create a new field service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new custom field.
     *
     * @throws FieldCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function create(FieldDTO $field): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->fields->create($field->toArray());

            return $this->transformFieldResponse($response);
        } catch (\Exception $e) {
            $this->handleCreateException($field->name, $e);
        }
    }

    /**
     * Get a field by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function get(string $id): ?array
    {
        return $this->getById($id);
    }

    /**
     * Get a field by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->fields->find($id);

            return $response ? $this->transformFieldResponse($response) : null;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Update an existing field.
     *
     * @throws FieldNotFoundException
     * @throws FieldUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, FieldDTO $field): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->fields->update($id, $field->toArray());

            return $this->transformFieldResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw FieldNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete a field.
     *
     * @throws FieldNotFoundException
     * @throws FieldDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->fields->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw FieldNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all fields with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->fields->get($filters);

            return [
                'data' => array_map([$this, 'transformFieldResponse'], $response['data'] ?? []),
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Find a field by name.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function findByName(string $name): ?array
    {
        try {
            $fields = $this->list();

            foreach ($fields['data'] as $field) {
                if ($field['name'] === $name) {
                    return $field;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get field usage statistics.
     *
     * @throws FieldNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getUsage(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->fields->usage($id);

            return [
                'subscribers_count' => $response['subscribers_count'] ?? 0,
                'filled_count' => $response['filled_count'] ?? 0,
                'empty_count' => $response['empty_count'] ?? 0,
                'usage_percentage' => $response['usage_percentage'] ?? 0.0,
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw FieldNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Transform field response data.
     */
    protected function transformFieldResponse(array $response): array
    {
        return [
            'id' => $response['id'] ?? null,
            'name' => $response['name'] ?? null,
            'type' => $response['type'] ?? null,
            'title' => $response['title'] ?? null,
            'default_value' => $response['default_value'] ?? null,
            'options' => $response['options'] ?? [],
            'required' => $response['required'] ?? false,
            'position' => $response['position'] ?? null,
            'subscribers_count' => $response['subscribers_count'] ?? null,
            'created_at' => $response['created_at'] ?? null,
            'updated_at' => $response['updated_at'] ?? null,
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
     * Handle field creation exceptions.
     *
     * @throws FieldCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $name, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'already exists') || str_contains($message, 'duplicate')) {
            throw FieldCreateException::alreadyExists($name);
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw FieldCreateException::invalidData($name, ['Validation failed']);
        }

        throw FieldCreateException::make($name, $e->getMessage(), $e);
    }

    /**
     * Handle field update exceptions.
     *
     * @throws FieldUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw FieldUpdateException::invalidData($identifier, ['Validation failed']);
        }

        throw FieldUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle field deletion exceptions.
     *
     * @throws FieldDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        throw FieldDeleteException::make($identifier, $e->getMessage(), $e);
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
