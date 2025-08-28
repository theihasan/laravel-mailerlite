<?php

declare(strict_types=1);

use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;

describe('CampaignDTO', function () {
    it('can be created with required fields', function () {
        $dto = new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        );

        expect($dto->subject)->toBe('Test Subject');
        expect($dto->fromName)->toBe('Test Sender');
        expect($dto->fromEmail)->toBe('sender@example.com');
        expect($dto->html)->toBe('<h1>Hello World</h1>');
        expect($dto->plain)->toBeNull();
        expect($dto->groups)->toBe([]);
        expect($dto->segments)->toBe([]);
        expect($dto->scheduleAt)->toBeNull();
        expect($dto->type)->toBe('regular');
    });

    it('can be created with all fields', function () {
        $scheduleAt = new DateTime('+1 hour');
        $groups = ['group1', 'group2'];
        $segments = ['segment1'];
        $settings = ['key' => 'value'];
        $abSettings = ['test_type' => 'subject', 'send_size' => 25];

        $dto = new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            plain: 'Hello World',
            groups: $groups,
            segments: $segments,
            scheduleAt: $scheduleAt,
            type: 'ab',
            settings: $settings,
            abSettings: $abSettings
        );

        expect($dto->subject)->toBe('Test Subject');
        expect($dto->fromName)->toBe('Test Sender');
        expect($dto->fromEmail)->toBe('sender@example.com');
        expect($dto->html)->toBe('<h1>Hello World</h1>');
        expect($dto->plain)->toBe('Hello World');
        expect($dto->groups)->toBe($groups);
        expect($dto->segments)->toBe($segments);
        expect($dto->scheduleAt)->toBe($scheduleAt);
        expect($dto->type)->toBe('ab');
        expect($dto->settings)->toBe($settings);
        expect($dto->abSettings)->toBe($abSettings);
    });

    it('validates subject is not empty', function () {
        expect(fn () => new CampaignDTO(
            subject: '',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'Campaign subject cannot be empty.');
    });

    it('validates subject length', function () {
        $longSubject = str_repeat('a', 256);

        expect(fn () => new CampaignDTO(
            subject: $longSubject,
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'Campaign subject cannot exceed 255 characters.');
    });

    it('validates from name is not empty', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: '',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'From name cannot be empty.');
    });

    it('validates from name length', function () {
        $longName = str_repeat('a', 101);

        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: $longName,
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'From name cannot exceed 100 characters.');
    });

    it('validates from email is not empty', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: '',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'From email cannot be empty.');
    });

    it('validates from email format', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'invalid-email',
            html: '<h1>Hello World</h1>'
        ))->toThrow(InvalidArgumentException::class, 'Invalid from email address: invalid-email');
    });

    it('validates content is provided', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com'
        ))->toThrow(InvalidArgumentException::class, 'Campaign must have either HTML or plain text content.');
    });

    it('validates html content is not empty if provided', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '   '
        ))->toThrow(InvalidArgumentException::class, 'HTML content cannot be empty if provided.');
    });

    it('validates plain content is not empty if provided', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            plain: '   '
        ))->toThrow(InvalidArgumentException::class, 'Plain text content cannot be empty if provided.');
    });

    it('validates group IDs are strings or integers', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            groups: [null]
        ))->toThrow(InvalidArgumentException::class, 'Group IDs must be strings or integers.');
    });

    it('validates group IDs are not empty strings', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            groups: ['']
        ))->toThrow(InvalidArgumentException::class, 'Group IDs cannot be empty strings.');
    });

    it('validates segment IDs are strings or integers', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            segments: [null]
        ))->toThrow(InvalidArgumentException::class, 'Segment IDs must be strings or integers.');
    });

    it('validates segment IDs are not empty strings', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            segments: ['']
        ))->toThrow(InvalidArgumentException::class, 'Segment IDs cannot be empty strings.');
    });

    it('validates campaign type', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'invalid'
        ))->toThrow(InvalidArgumentException::class, "Invalid campaign type 'invalid'. Valid types: regular, ab, resend");
    });

    it('validates schedule time is in the future', function () {
        $pastTime = new DateTime('-1 hour');

        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            scheduleAt: $pastTime
        ))->toThrow(InvalidArgumentException::class, 'Schedule time must be in the future.');
    });

    it('validates ab settings are required for ab campaign type', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'ab'
        ))->toThrow(InvalidArgumentException::class, 'A/B test settings are required when campaign type is "ab".');
    });

    it('validates ab settings cannot be used with non-ab campaign type', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'regular',
            abSettings: ['test_type' => 'subject', 'send_size' => 25]
        ))->toThrow(InvalidArgumentException::class, 'A/B test settings can only be used with campaign type "ab".');
    });

    it('validates ab settings required fields', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'ab',
            abSettings: ['test_type' => 'subject']
        ))->toThrow(InvalidArgumentException::class, "A/B test setting 'send_size' is required.");
    });

    it('validates ab test type', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'ab',
            abSettings: ['test_type' => 'invalid', 'send_size' => 25]
        ))->toThrow(InvalidArgumentException::class, "Invalid A/B test type 'invalid'. Valid types: subject, from_name, content");
    });

    it('validates ab send size range', function () {
        expect(fn () => new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            type: 'ab',
            abSettings: ['test_type' => 'subject', 'send_size' => 5]
        ))->toThrow(InvalidArgumentException::class, 'A/B test send size must be an integer between 10 and 50.');
    });

    it('can be created from array', function () {
        $data = [
            'subject' => 'Test Subject',
            'from_name' => 'Test Sender',
            'from_email' => 'sender@example.com',
            'html' => '<h1>Hello World</h1>',
            'plain' => 'Hello World',
            'groups' => ['group1'],
            'segments' => ['segment1'],
            'schedule_at' => new DateTime('+1 hour'),
            'type' => 'regular',
            'settings' => ['key' => 'value'],
            'ab_settings' => [],
        ];

        $dto = CampaignDTO::fromArray($data);

        expect($dto->subject)->toBe('Test Subject');
        expect($dto->fromName)->toBe('Test Sender');
        expect($dto->fromEmail)->toBe('sender@example.com');
        expect($dto->html)->toBe('<h1>Hello World</h1>');
        expect($dto->plain)->toBe('Hello World');
        expect($dto->groups)->toBe(['group1']);
        expect($dto->segments)->toBe(['segment1']);
        expect($dto->type)->toBe('regular');
    });

    it('can be created from array with string schedule_at', function () {
        $data = [
            'subject' => 'Test Subject',
            'from_name' => 'Test Sender',
            'from_email' => 'sender@example.com',
            'html' => '<h1>Hello World</h1>',
            'schedule_at' => '+1 hour',
        ];

        $dto = CampaignDTO::fromArray($data);

        expect($dto->scheduleAt)->toBeInstanceOf(DateTime::class);
    });

    it('can convert to array', function () {
        $scheduleAt = new DateTime('+1 hour');

        $dto = new CampaignDTO(
            subject: 'Test Subject',
            fromName: 'Test Sender',
            fromEmail: 'sender@example.com',
            html: '<h1>Hello World</h1>',
            plain: 'Hello World',
            groups: ['group1'],
            segments: ['segment1'],
            scheduleAt: $scheduleAt,
            type: 'regular',
            settings: ['key' => 'value'],
            abSettings: []
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'subject' => 'Test Subject',
            'from_name' => 'Test Sender',
            'from_email' => 'sender@example.com',
            'html' => '<h1>Hello World</h1>',
            'plain' => 'Hello World',
            'groups' => ['group1'],
            'segments' => ['segment1'],
            'schedule_at' => $scheduleAt->format('Y-m-d H:i:s'),
            'type' => 'regular',
            'settings' => ['key' => 'value'],
        ]);
    });

    it('has factory methods', function () {
        $dto = CampaignDTO::create('Subject', 'Sender', 'sender@example.com');
        expect($dto->subject)->toBe('Subject');
        expect($dto->fromName)->toBe('Sender');
        expect($dto->fromEmail)->toBe('sender@example.com');

        $dto = CampaignDTO::createWithHtml('Subject', 'Sender', 'sender@example.com', '<h1>Test</h1>');
        expect($dto->html)->toBe('<h1>Test</h1>');

        $dto = CampaignDTO::createWithContent('Subject', 'Sender', 'sender@example.com', '<h1>Test</h1>', 'Test');
        expect($dto->html)->toBe('<h1>Test</h1>');
        expect($dto->plain)->toBe('Test');
    });

    it('has immutable with methods', function () {
        $original = new CampaignDTO(
            subject: 'Original Subject',
            fromName: 'Original Sender',
            fromEmail: 'original@example.com',
            html: '<h1>Original</h1>'
        );

        $withSubject = $original->withSubject('New Subject');
        expect($original->subject)->toBe('Original Subject');
        expect($withSubject->subject)->toBe('New Subject');

        $withFrom = $original->withFrom('New Sender', 'new@example.com');
        expect($original->fromName)->toBe('Original Sender');
        expect($withFrom->fromName)->toBe('New Sender');
        expect($withFrom->fromEmail)->toBe('new@example.com');

        $withHtml = $original->withHtml('<h1>New HTML</h1>');
        expect($original->html)->toBe('<h1>Original</h1>');
        expect($withHtml->html)->toBe('<h1>New HTML</h1>');

        $withGroups = $original->withGroups(['group1', 'group2']);
        expect($original->groups)->toBe([]);
        expect($withGroups->groups)->toBe(['group1', 'group2']);

        $withSegments = $original->withSegments(['segment1']);
        expect($original->segments)->toBe([]);
        expect($withSegments->segments)->toBe(['segment1']);

        $scheduleAt = new DateTime('+1 hour');
        $withSchedule = $original->withSchedule($scheduleAt);
        expect($original->scheduleAt)->toBeNull();
        expect($withSchedule->scheduleAt)->toBe($scheduleAt);
    });
});
