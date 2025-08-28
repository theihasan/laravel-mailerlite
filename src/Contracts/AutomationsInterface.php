<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Contracts;

use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;

/**
 * Automation service contract.
 *
 * This interface defines the contract for automation-related operations,
 * including creating, managing, and monitoring automations.
 */
interface AutomationsInterface
{
    /**
     * Create a new automation.
     *
     * @param AutomationDTO $automation
     * @return array
     */
    public function create(AutomationDTO $automation): array;

    /**
     * Get an automation by ID.
     *
     * @param string $id
     * @return array|null
     */
    public function getById(string $id): ?array;

    /**
     * Update an existing automation.
     *
     * @param string $id
     * @param AutomationDTO $automation
     * @return array
     */
    public function update(string $id, AutomationDTO $automation): array;

    /**
     * Delete an automation.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Get all automations with optional filtering.
     *
     * @param array $filters
     * @return array
     */
    public function list(array $filters = []): array;

    /**
     * Start/enable an automation.
     *
     * @param string $id
     * @return array
     */
    public function start(string $id): array;

    /**
     * Stop/disable an automation.
     *
     * @param string $id
     * @return array
     */
    public function stop(string $id): array;

    /**
     * Get subscribers in an automation.
     *
     * @param string $id
     * @param array $filters
     * @return array
     */
    public function getSubscribers(string $id, array $filters = []): array;

    /**
     * Get automation activity/stats.
     *
     * @param string $id
     * @param array $filters
     * @return array
     */
    public function getActivity(string $id, array $filters = []): array;

    /**
     * Get automation statistics.
     *
     * @param string $id
     * @return array
     */
    public function getStats(string $id): array;
}
