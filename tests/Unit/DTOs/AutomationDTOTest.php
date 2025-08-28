<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;

describe('AutomationDTO', function () {
    it('can be created with required fields', function () {
        $triggers = [
            ['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter'],
        ];

        $steps = [
            ['type' => 'email', 'template_id' => 'welcome-template'],
        ];

        $dto = new AutomationDTO(
            name: 'Test Automation',
            triggers: $triggers,
            steps: $steps
        );

        expect($dto->name)->toBe('Test Automation');
        expect($dto->enabled)->toBeTrue();
        expect($dto->triggers)->toBe($triggers);
        expect($dto->steps)->toBe($steps);
        expect($dto->description)->toBeNull();
        expect($dto->settings)->toBe([]);
        expect($dto->conditions)->toBe([]);
        expect($dto->status)->toBe('draft');
    });

    it('can be created with all fields', function () {
        $triggers = [
            ['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter'],
        ];

        $steps = [
            ['type' => 'email', 'template_id' => 'welcome-template'],
            ['type' => 'delay', 'duration' => 1, 'unit' => 'days'],
        ];

        $settings = ['timezone' => 'America/New_York'];
        $conditions = [['field' => 'country', 'operator' => 'equals', 'value' => 'US']];

        $dto = new AutomationDTO(
            name: 'Test Automation',
            enabled: false,
            triggers: $triggers,
            steps: $steps,
            description: 'Test description',
            settings: $settings,
            conditions: $conditions,
            status: 'active'
        );

        expect($dto->name)->toBe('Test Automation');
        expect($dto->enabled)->toBeFalse();
        expect($dto->triggers)->toBe($triggers);
        expect($dto->steps)->toBe($steps);
        expect($dto->description)->toBe('Test description');
        expect($dto->settings)->toBe($settings);
        expect($dto->conditions)->toBe($conditions);
        expect($dto->status)->toBe('active');
    });

    it('validates name is not empty', function () {
        expect(fn () => new AutomationDTO(
            name: '',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, 'Automation name cannot be empty.');
    });

    it('validates name length', function () {
        $longName = str_repeat('a', 256);

        expect(fn () => new AutomationDTO(
            name: $longName,
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, 'Automation name cannot exceed 255 characters.');
    });

    it('validates triggers are provided', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, 'Automation must have at least one trigger.');
    });

    it('validates trigger structure', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: ['invalid'],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, 'Trigger at index 0 must be an array.');

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Trigger at index 0 must have a 'type' field.");
    });

    it('validates trigger types', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'invalid', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Invalid trigger type 'invalid' at index 0. Valid types: subscriber, date, api, webhook, custom_field");
    });

    it('validates subscriber trigger events', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Subscriber trigger at index 0 must have an 'event' field.");

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'invalid']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Invalid subscriber event 'invalid' at index 0. Valid events: joins_group, subscribes, unsubscribes, updates_field, completes_automation");
    });

    it('validates date trigger fields', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'date']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Date trigger at index 0 must have a 'field' field.");

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'date', 'field' => 'birthday']],
            steps: [['type' => 'email', 'template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Date trigger at index 0 must have an 'offset' field.");
    });

    it('validates steps are provided', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: []
        ))->toThrow(InvalidArgumentException::class, 'Automation must have at least one step.');
    });

    it('validates step structure', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: ['invalid']
        ))->toThrow(InvalidArgumentException::class, 'Step at index 0 must be an array.');

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['template_id' => 'test']]
        ))->toThrow(InvalidArgumentException::class, "Step at index 0 must have a 'type' field.");
    });

    it('validates step types', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'invalid']]
        ))->toThrow(InvalidArgumentException::class, "Invalid step type 'invalid' at index 0. Valid types: email, delay, condition, action, webhook, tag, field_update");
    });

    it('validates email step requirements', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email']]
        ))->toThrow(InvalidArgumentException::class, "Email step at index 0 must have either 'campaign_id' or 'template_id'.");
    });

    it('validates delay step requirements', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'delay']]
        ))->toThrow(InvalidArgumentException::class, "Delay step at index 0 must have 'duration' and 'unit' fields.");

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'delay', 'duration' => 1, 'unit' => 'invalid']]
        ))->toThrow(InvalidArgumentException::class, "Invalid delay unit 'invalid' at index 0. Valid units: minutes, hours, days, weeks");
    });

    it('validates webhook step requirements', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'webhook']]
        ))->toThrow(InvalidArgumentException::class, "Webhook step at index 0 must have 'url' field.");

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'webhook', 'url' => 'invalid-url']]
        ))->toThrow(InvalidArgumentException::class, 'Invalid webhook URL at step index 0.');
    });

    it('validates automation status', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']],
            status: 'invalid'
        ))->toThrow(InvalidArgumentException::class, "Invalid status 'invalid'. Valid statuses: draft, active, paused, completed, disabled");
    });

    it('validates automation conditions', function () {
        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']],
            conditions: ['invalid']
        ))->toThrow(InvalidArgumentException::class, 'Condition at index 0 must be an array.');

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']],
            conditions: [['field' => 'country']]
        ))->toThrow(InvalidArgumentException::class, "Condition at index 0 must have 'field', 'operator', and 'value' fields.");

        expect(fn () => new AutomationDTO(
            name: 'Test Automation',
            triggers: [['type' => 'subscriber', 'event' => 'joins_group']],
            steps: [['type' => 'email', 'template_id' => 'test']],
            conditions: [['field' => 'country', 'operator' => 'invalid', 'value' => 'US']]
        ))->toThrow(InvalidArgumentException::class, "Invalid condition operator 'invalid' at index 0. Valid operators: equals, not_equals, contains, not_contains, greater_than, less_than, exists, not_exists");
    });

    it('can be created from array', function () {
        $data = [
            'name' => 'Test Automation',
            'enabled' => false,
            'triggers' => [['type' => 'subscriber', 'event' => 'joins_group']],
            'steps' => [['type' => 'email', 'template_id' => 'test']],
            'description' => 'Test description',
            'settings' => ['timezone' => 'UTC'],
            'conditions' => [['field' => 'country', 'operator' => 'equals', 'value' => 'US']],
            'status' => 'active',
        ];

        $dto = AutomationDTO::fromArray($data);

        expect($dto->name)->toBe('Test Automation');
        expect($dto->enabled)->toBeFalse();
        expect($dto->description)->toBe('Test description');
        expect($dto->status)->toBe('active');
    });

    it('can convert to array', function () {
        $triggers = [['type' => 'subscriber', 'event' => 'joins_group']];
        $steps = [['type' => 'email', 'template_id' => 'test']];
        $settings = ['timezone' => 'UTC'];
        $conditions = [['field' => 'country', 'operator' => 'equals', 'value' => 'US']];

        $dto = new AutomationDTO(
            name: 'Test Automation',
            enabled: false,
            triggers: $triggers,
            steps: $steps,
            description: 'Test description',
            settings: $settings,
            conditions: $conditions,
            status: 'active'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'name' => 'Test Automation',
            'enabled' => false,
            'status' => 'active',
            'description' => 'Test description',
            'triggers' => $triggers,
            'steps' => $steps,
            'settings' => $settings,
            'conditions' => $conditions,
        ]);
    });

    it('has factory methods', function () {
        $dto = AutomationDTO::create('Basic Automation');
        expect($dto->name)->toBe('Basic Automation');
        expect($dto->enabled)->toBeTrue();

        $triggers = [['type' => 'subscriber', 'event' => 'joins_group']];
        $steps = [['type' => 'email', 'template_id' => 'test']];

        $dto = AutomationDTO::createWithFlow('Flow Automation', $triggers, $steps);
        expect($dto->name)->toBe('Flow Automation');
        expect($dto->triggers)->toBe($triggers);
        expect($dto->steps)->toBe($steps);

        $steps = [['type' => 'email', 'template_id' => 'welcome']];
        $dto = AutomationDTO::createSubscriberAutomation('Subscriber Automation', 'joins_group', $steps);
        expect($dto->name)->toBe('Subscriber Automation');
        expect($dto->triggers)->toBe([['type' => 'subscriber', 'event' => 'joins_group']]);
        expect($dto->steps)->toBe($steps);

        $steps = [['type' => 'email', 'template_id' => 'birthday']];
        $dto = AutomationDTO::createDateAutomation('Birthday Automation', 'birthday', 0, $steps);
        expect($dto->name)->toBe('Birthday Automation');
        expect($dto->triggers)->toBe([['type' => 'date', 'field' => 'birthday', 'offset' => 0, 'unit' => 'days']]);
        expect($dto->steps)->toBe($steps);
    });

    it('has immutable with methods', function () {
        $triggers = [['type' => 'subscriber', 'event' => 'joins_group']];
        $steps = [['type' => 'email', 'template_id' => 'test']];

        $original = new AutomationDTO(
            name: 'Original Automation',
            triggers: $triggers,
            steps: $steps
        );

        $withName = $original->withName('New Name');
        expect($original->name)->toBe('Original Automation');
        expect($withName->name)->toBe('New Name');

        $withEnabled = $original->withEnabled(false);
        expect($original->enabled)->toBeTrue();
        expect($withEnabled->enabled)->toBeFalse();

        $withDescription = $original->withDescription('New description');
        expect($original->description)->toBeNull();
        expect($withDescription->description)->toBe('New description');

        $newTriggers = [['type' => 'date', 'field' => 'birthday', 'offset' => 0]];
        $withTriggers = $original->withTriggers($newTriggers);
        expect($original->triggers)->toBe($triggers);
        expect($withTriggers->triggers)->toBe($newTriggers);

        $newSteps = [['type' => 'delay', 'duration' => 1, 'unit' => 'days']];
        $withSteps = $original->withSteps($newSteps);
        expect($original->steps)->toBe($steps);
        expect($withSteps->steps)->toBe($newSteps);

        $withStatus = $original->withStatus('active');
        expect($original->status)->toBe('draft');
        expect($withStatus->status)->toBe('active');
    });
});
