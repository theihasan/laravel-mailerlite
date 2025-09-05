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
            // MailerLite API doesn't have a single field endpoint, so we search through all fields
            $fields = $this->list();

            foreach ($fields['data'] as $field) {
                if ($field['id'] === $id) {
                    return $field;
                }
            }

            return null;
        } catch (\Exception $e) {
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
            
            // The MailerLite SDK wraps the API response in a 'body' key
            $body = $response['body'] ?? $response;

            return [
                'data' => array_map([$this, 'transformFieldResponse'], $body['data'] ?? []),
                'meta' => $body['meta'] ?? [],
                'links' => $body['links'] ?? [],
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
     * Note: This functionality is not available in the current MailerLite API.
     * 
     * @throws \BadMethodCallException
     */
    public function getUsage(string $id): array
    {
        throw new \BadMethodCallException(
            'Field usage statistics are not available in the MailerLite API. ' .
            'This method is not implemented.'
        );
    }

    /**
     * Transform field response data.
     */
    protected function transformFieldResponse(array $response): array
    {
        $data = $response['body']['data'] ?? $response;
        
        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? null,
            'key' => $data['key'] ?? null,
            'type' => $data['type'] ?? null,
            'title' => $data['title'] ?? null,
            'default_value' => $data['default_value'] ?? null,
            'options' => $data['options'] ?? [],
            'required' => $data['required'] ?? false,
            'position' => $data['position'] ?? null,
            'subscribers_count' => $data['subscribers_count'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'used_in_automations' => $data['used_in_automations'] ?? false,
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
