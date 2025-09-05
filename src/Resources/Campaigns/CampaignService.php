<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Campaigns;

use Ihasan\LaravelMailerlite\Contracts\CampaignsInterface;
use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;
use Ihasan\LaravelMailerlite\Exceptions\CampaignCreateException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignDeleteException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignNotFoundException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignSendException;
use Ihasan\LaravelMailerlite\Exceptions\CampaignUpdateException;
use Ihasan\LaravelMailerlite\Exceptions\MailerLiteAuthenticationException;
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

/**
 * Campaign Service
 *
 * This service handles all campaign-related operations with the MailerLite API.
 * It implements the CampaignsInterface and provides comprehensive error handling
 * and data transformation.
 */
class CampaignService implements CampaignsInterface
{
    /**
     * Create a new campaign service instance.
     */
    public function __construct(
        protected MailerLiteManager $manager
    ) {}

    /**
     * Create a new campaign.
     *
     * @throws CampaignCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function create(CampaignDTO $campaign): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->create($campaign->toArray());

            return $this->transformCampaignResponse($response);
        } catch (\Exception $e) {
            $this->handleCreateException($campaign->subject, $e);
        }
    }

    /**
     * Get a campaign by ID.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function getById(string $id): ?array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->find($id);

            return $response ? $this->transformCampaignResponse($response) : null;
        } catch (\Exception $e) {
            // If it's a 404, return null instead of throwing
            if ($this->isNotFoundError($e)) {
                return null;
            }

            $this->handleException($e);
        }
    }

    /**
     * Find a campaign by name.
     * 
     * Note: Searches through paginated campaigns list to find matching name.
     * This provides a workaround for MailerLite API limitations.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function findByName(string $name): ?array
    {
        try {
            // Search through all campaigns to find one with matching name
            $campaigns = $this->list();
            
            foreach ($campaigns['data'] as $campaign) {
                if ($campaign['name'] === $name) {
                    return $campaign;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update an existing campaign.
     *
     * @throws CampaignNotFoundException
     * @throws CampaignUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function update(string $id, CampaignDTO $campaign): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->update($id, $campaign->toArray());

            return $this->transformCampaignResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Delete a campaign.
     *
     * @throws CampaignNotFoundException
     * @throws CampaignDeleteException
     * @throws MailerLiteAuthenticationException
     */
    public function delete(string $id): bool
    {
        try {
            $client = $this->manager->getClient();
            $client->campaigns->delete($id);

            return true;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleDeleteException($id, $e);
        }
    }

    /**
     * Get all campaigns with optional filtering.
     *
     * @throws MailerLiteAuthenticationException
     */
    public function list(array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->get($filters);
            
            // The MailerLite SDK wraps the API response in a 'body' key
            $body = $response['body'] ?? $response;

            return [
                'data' => array_map([$this, 'transformCampaignResponse'], $body['data'] ?? []),
                'meta' => $body['meta'] ?? [],
                'links' => $body['links'] ?? [],
            ];
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Schedule a campaign to be sent at a specific time.
     *
     * @throws CampaignNotFoundException
     * @throws CampaignSendException
     * @throws MailerLiteAuthenticationException
     */
    public function schedule(string $id, \DateTimeInterface $scheduledAt): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->schedule($id, [
                'schedule_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ]);

            return $this->transformCampaignResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleSendException($id, $e);
        }
    }

    /**
     * Send a campaign immediately.
     *
     * @throws CampaignNotFoundException
     * @throws CampaignSendException
     * @throws MailerLiteAuthenticationException
     */
    public function send(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->send($id);

            return $this->transformCampaignResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleSendException($id, $e);
        }
    }

    /**
     * Cancel a scheduled campaign.
     *
     * @throws CampaignNotFoundException
     * @throws CampaignUpdateException
     * @throws MailerLiteAuthenticationException
     */
    public function cancel(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->cancel($id);

            return $this->transformCampaignResponse($response);
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleUpdateException($id, $e);
        }
    }

    /**
     * Get campaign statistics.
     *
     * @throws CampaignNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getStats(string $id): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->getStats($id);

            return $response;
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Get campaign subscribers.
     *
     * @throws CampaignNotFoundException
     * @throws MailerLiteAuthenticationException
     */
    public function getSubscribers(string $id, array $filters = []): array
    {
        try {
            $client = $this->manager->getClient();
            $response = $client->campaigns->getSubscribers($id, $filters);

            return [
                'data' => $response['data'] ?? [],
                'meta' => $response['meta'] ?? [],
                'links' => $response['links'] ?? [],
            ];
        } catch (\Exception $e) {
            if ($this->isNotFoundError($e)) {
                throw CampaignNotFoundException::withId($id);
            }

            $this->handleException($e);
        }
    }

    /**
     * Create a draft campaign (alias for create).
     *
     * @throws CampaignCreateException
     * @throws MailerLiteAuthenticationException
     */
    public function draft(CampaignDTO $campaign): array
    {
        return $this->create($campaign);
    }

    /**
     * Transform campaign response data.
     */
    protected function transformCampaignResponse(array $response): array
    {
        $data = $response['body']['data'] ?? $response;
        
        return [
            'id' => $data['id'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'name' => $data['name'] ?? null,
            'subject' => $data['subject'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? null,
            'status' => $data['status'] ?? null,
            'type' => $data['type'] ?? null,
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'sent_at' => $data['sent_at'] ?? null,
            'delivery_schedule' => $data['delivery_schedule'] ?? null,
            'language_iso' => $data['language_iso'] ?? null,
            'is_winner' => $data['is_winner'] ?? null,
            'winner_version_for' => $data['winner_version_for'] ?? null,
            'winner_sending_time' => $data['winner_sending_time'] ?? null,
            'winner_selected_manually_at' => $data['winner_selected_manually_at'] ?? null,
            'uses_ecommerce' => $data['uses_ecommerce'] ?? null,
            'uses_survey' => $data['uses_survey'] ?? null,
            'can_be_scheduled' => $data['can_be_scheduled'] ?? null,
            'warnings' => $data['warnings'] ?? [],
            'initial_created_at' => $data['initial_created_at'] ?? null,
            'emails' => $data['emails'] ?? [],
            'used_in_automations' => $data['used_in_automations'] ?? [],
            'type_for_humans' => $data['type_for_humans'] ?? null,
            'stats' => $data['stats'] ?? [],
            'settings' => $data['settings'] ?? [],
            'ab_settings' => $data['ab_settings'] ?? [],
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
     * Handle campaign creation exceptions.
     *
     * @throws CampaignCreateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleCreateException(string $subject, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw CampaignCreateException::invalidData($subject, ['Validation failed']);
        }

        throw CampaignCreateException::make($subject, $e->getMessage(), $e);
    }

    /**
     * Handle campaign update exceptions.
     *
     * @throws CampaignUpdateException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleUpdateException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, '422') || str_contains($message, 'validation')) {
            throw CampaignUpdateException::invalidData($identifier, ['Validation failed']);
        }

        if (str_contains($message, 'cannot be updated') || str_contains($message, 'sent')) {
            throw CampaignUpdateException::cannotUpdate($identifier, 'sent');
        }

        throw CampaignUpdateException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle campaign deletion exceptions.
     *
     * @throws CampaignDeleteException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleDeleteException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'cannot be deleted') || str_contains($message, 'sent')) {
            throw CampaignDeleteException::cannotDelete($identifier, 'sent');
        }

        throw CampaignDeleteException::make($identifier, $e->getMessage(), $e);
    }

    /**
     * Handle campaign send exceptions.
     *
     * @throws CampaignSendException
     * @throws MailerLiteAuthenticationException
     */
    protected function handleSendException(string $identifier, \Exception $e): never
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, '401') || str_contains($message, 'unauthorized')) {
            throw MailerLiteAuthenticationException::invalidApiKey();
        }

        if (str_contains($message, 'no recipients') || str_contains($message, 'empty')) {
            throw CampaignSendException::noRecipients($identifier);
        }

        if (str_contains($message, 'cannot be sent') || str_contains($message, 'already sent')) {
            throw CampaignSendException::cannotSend($identifier, 'sent');
        }

        throw CampaignSendException::make($identifier, $e->getMessage(), $e);
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
