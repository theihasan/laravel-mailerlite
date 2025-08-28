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
     */
    public function create(AutomationDTO $automation): array;

    /**
     * Get an automation by ID.
     */
    public function getById(string $id): ?array;

    /**
     * Update an existing automation.
     */
    public function update(string $id, AutomationDTO $automation): array;

    /**
     * Delete an automation.
     */
    public function delete(string $id): bool;

    /**
     * Get all automations with optional filtering.
     */
    public function list(array $filters = []): array;

    /**
     * Start/enable an automation.
     */
    public function start(string $id): array;

    /**
     * Stop/disable an automation.
     */
    public function stop(string $id): array;

    /**
     * Get subscribers in an automation.
     */
    public function getSubscribers(string $id, array $filters = []): array;

    /**
     * Get automation activity/stats.
     */
    public function getActivity(string $id, array $filters = []): array;

    /**
     * Get automation statistics.
     */
    public function getStats(string $id): array;
}
