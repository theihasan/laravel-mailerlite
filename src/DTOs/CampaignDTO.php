<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\DTOs;

use InvalidArgumentException;

/**
 * Campaign Data Transfer Object
 *
 * This class represents campaign data with validation and normalization.
 * It ensures that campaign information is properly formatted and valid
 * before being sent to the MailerLite API.
 */
class CampaignDTO
{
    /**
     * Create a new campaign DTO.
     *
     * @param  string  $name  Campaign name (required, max 255 chars)
     * @param  string  $subject  Email subject line (required)
     * @param  string  $fromName  Sender name (required)
     * @param  string  $fromEmail  Sender email address (required)
     * @param  string|null  $html  HTML content of the campaign (optional)
     * @param  string|null  $plain  Plain text content of the campaign (optional)
     * @param  array  $groups  Group IDs to send campaign to (optional)
     * @param  array  $segments  Segment IDs to send campaign to (optional)
     * @param  \DateTimeInterface|null  $scheduleAt  When to schedule the campaign (optional)
     * @param  string  $type  Campaign type: regular, ab, resend (default: regular)
     * @param  array  $settings  Additional campaign settings (optional)
     * @param  array  $abSettings  A/B test settings when type is 'ab' (optional)
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $name,
        public readonly string $subject,
        public readonly string $fromName,
        public readonly string $fromEmail,
        public readonly ?string $html = null,
        public readonly ?string $plain = null,
        public readonly array $groups = [],
        public readonly array $segments = [],
        public readonly ?\DateTimeInterface $scheduleAt = null,
        public readonly string $type = 'regular',
        public readonly array $settings = [],
        public readonly array $abSettings = [],
    ) {
        $this->validateName($name);
        $this->validateSubject($subject);
        $this->validateFromName($fromName);
        $this->validateFromEmail($fromEmail);
        $this->validateContent($html, $plain);
        $this->validateGroups($groups);
        $this->validateSegments($segments);
        $this->validateType($type);
        $this->validateScheduleAt($scheduleAt);
        $this->validateAbSettings($type, $abSettings);
    }

    /**
     * Create a campaign DTO from an array.
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $data): static
    {
        $scheduleAt = null;
        if (isset($data['schedule_at'])) {
            if ($data['schedule_at'] instanceof \DateTimeInterface) {
                $scheduleAt = $data['schedule_at'];
            } elseif (is_string($data['schedule_at'])) {
                $scheduleAt = new \DateTime($data['schedule_at']);
            }
        }

        return new static(
            name: $data['name'] ?? throw new InvalidArgumentException('Name is required'),
            subject: $data['subject'] ?? throw new InvalidArgumentException('Subject is required'),
            fromName: $data['from_name'] ?? throw new InvalidArgumentException('From name is required'),
            fromEmail: $data['from_email'] ?? throw new InvalidArgumentException('From email is required'),
            html: $data['html'] ?? null,
            plain: $data['plain'] ?? null,
            groups: $data['groups'] ?? [],
            segments: $data['segments'] ?? [],
            scheduleAt: $scheduleAt,
            type: $data['type'] ?? 'regular',
            settings: $data['settings'] ?? [],
            abSettings: $data['ab_settings'] ?? [],
        );
    }

    /**
     * Create a basic campaign with name, subject, from name, and from email.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $name, string $subject, string $fromName, string $fromEmail): static
    {
        return new static(
            name: $name,
            subject: $subject,
            fromName: $fromName,
            fromEmail: $fromEmail
        );
    }

    /**
     * Create a campaign with HTML content.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithHtml(string $name, string $subject, string $fromName, string $fromEmail, string $html): static
    {
        return new static(
            name: $name,
            subject: $subject,
            fromName: $fromName,
            fromEmail: $fromEmail,
            html: $html
        );
    }

    /**
     * Create a campaign with both HTML and plain text content.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithContent(string $name, string $subject, string $fromName, string $fromEmail, string $html, string $plain): static
    {
        return new static(
            name: $name,
            subject: $subject,
            fromName: $fromName,
            fromEmail: $fromEmail,
            html: $html,
            plain: $plain
        );
    }

    /**
     * Convert the DTO to an array for API submission.
     * Follows MailerLite API structure with required 'emails' array.
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'emails' => [
                [
                    'subject' => $this->subject,
                    'from_name' => $this->fromName,
                    'from' => $this->fromEmail,
                ]
            ]
        ];

        // Add content to the email object if provided
        if ($this->html !== null) {
            $data['emails'][0]['content'] = $this->html;
        }

        if ($this->plain !== null) {
            $data['emails'][0]['plain_text'] = $this->plain;
        }

        if (! empty($this->groups)) {
            $data['groups'] = $this->groups;
        }

        if (! empty($this->segments)) {
            $data['segments'] = $this->segments;
        }

        if ($this->scheduleAt !== null) {
            $data['schedule_at'] = $this->scheduleAt->format('Y-m-d H:i:s');
        }

        if (! empty($this->settings)) {
            $data['settings'] = $this->settings;
        }

        if (! empty($this->abSettings)) {
            $data['ab_settings'] = $this->abSettings;
        }

        return $data;
    }

    /**
     * Get a copy of the DTO with updated fields.
     *
     * @throws InvalidArgumentException
     */
    public function with(array $updates): static
    {
        return static::fromArray(array_merge($this->toArray(), $updates));
    }

    /**
     * Get a copy with a different name.
     */
    public function withName(string $name): static
    {
        return new static(
            name: $name,
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: $this->groups,
            segments: $this->segments,
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with a different subject.
     */
    public function withSubject(string $subject): static
    {
        return new static(
            name: $this->name,
            subject: $subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: $this->groups,
            segments: $this->segments,
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with different sender information.
     */
    public function withFrom(string $fromName, string $fromEmail): static
    {
        return new static(
            name: $this->name,
            subject: $this->subject,
            fromName: $fromName,
            fromEmail: $fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: $this->groups,
            segments: $this->segments,
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with HTML content.
     */
    public function withHtml(string $html): static
    {
        return new static(
            name: $this->name,
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $html,
            plain: $this->plain,
            groups: $this->groups,
            segments: $this->segments,
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with additional groups.
     */
    public function withGroups(array $groups): static
    {
        return new static(
            name: $this->name,
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: array_unique([...$this->groups, ...$groups]),
            segments: $this->segments,
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with additional segments.
     */
    public function withSegments(array $segments): static
    {
        return new static(
            name: $this->name,
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: $this->groups,
            segments: array_unique([...$this->segments, ...$segments]),
            scheduleAt: $this->scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Get a copy with a schedule time.
     */
    public function withSchedule(\DateTimeInterface $scheduleAt): static
    {
        return new static(
            name: $this->name,
            subject: $this->subject,
            fromName: $this->fromName,
            fromEmail: $this->fromEmail,
            html: $this->html,
            plain: $this->plain,
            groups: $this->groups,
            segments: $this->segments,
            scheduleAt: $scheduleAt,
            type: $this->type,
            settings: $this->settings,
            abSettings: $this->abSettings,
        );
    }

    /**
     * Validate campaign name.
     *
     * @throws InvalidArgumentException
     */
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('Campaign name cannot be empty.');
        }

        if (strlen($name) > 255) {
            throw new InvalidArgumentException('Campaign name cannot exceed 255 characters.');
        }
    }

    /**
     * Validate campaign subject.
     *
     * @throws InvalidArgumentException
     */
    private function validateSubject(string $subject): void
    {
        if (empty(trim($subject))) {
            throw new InvalidArgumentException('Campaign subject cannot be empty.');
        }

        if (strlen($subject) > 255) {
            throw new InvalidArgumentException('Campaign subject cannot exceed 255 characters.');
        }
    }

    /**
     * Validate sender name.
     *
     * @throws InvalidArgumentException
     */
    private function validateFromName(string $fromName): void
    {
        if (empty(trim($fromName))) {
            throw new InvalidArgumentException('From name cannot be empty.');
        }

        if (strlen($fromName) > 100) {
            throw new InvalidArgumentException('From name cannot exceed 100 characters.');
        }
    }

    /**
     * Validate sender email address.
     *
     * @throws InvalidArgumentException
     */
    private function validateFromEmail(string $fromEmail): void
    {
        if (empty(trim($fromEmail))) {
            throw new InvalidArgumentException('From email cannot be empty.');
        }

        if (! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid from email address: {$fromEmail}");
        }
    }

    /**
     * Validate campaign content.
     *
     * @throws InvalidArgumentException
     */
    private function validateContent(?string $html, ?string $plain): void
    {
        if ($html === null && $plain === null) {
            throw new InvalidArgumentException('Campaign must have either HTML or plain text content.');
        }

        if ($html !== null && empty(trim($html))) {
            throw new InvalidArgumentException('HTML content cannot be empty if provided.');
        }

        if ($plain !== null && empty(trim($plain))) {
            throw new InvalidArgumentException('Plain text content cannot be empty if provided.');
        }
    }

    /**
     * Validate groups array.
     *
     * @throws InvalidArgumentException
     */
    private function validateGroups(array $groups): void
    {
        foreach ($groups as $group) {
            if (! is_string($group) && ! is_int($group)) {
                throw new InvalidArgumentException('Group IDs must be strings or integers.');
            }

            if (is_string($group) && empty(trim($group))) {
                throw new InvalidArgumentException('Group IDs cannot be empty strings.');
            }
        }
    }

    /**
     * Validate segments array.
     *
     * @throws InvalidArgumentException
     */
    private function validateSegments(array $segments): void
    {
        foreach ($segments as $segment) {
            if (! is_string($segment) && ! is_int($segment)) {
                throw new InvalidArgumentException('Segment IDs must be strings or integers.');
            }

            if (is_string($segment) && empty(trim($segment))) {
                throw new InvalidArgumentException('Segment IDs cannot be empty strings.');
            }
        }
    }

    /**
     * Validate campaign type.
     *
     * @throws InvalidArgumentException
     */
    private function validateType(string $type): void
    {
        $validTypes = ['regular', 'ab', 'resend'];

        if (! in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                "Invalid campaign type '{$type}'. Valid types: ".implode(', ', $validTypes)
            );
        }
    }

    /**
     * Validate schedule time.
     *
     * @throws InvalidArgumentException
     */
    private function validateScheduleAt(?\DateTimeInterface $scheduleAt): void
    {
        if ($scheduleAt !== null) {
            $now = new \DateTime;
            if ($scheduleAt <= $now) {
                throw new InvalidArgumentException('Schedule time must be in the future.');
            }
        }
    }

    /**
     * Validate A/B test settings.
     *
     * @throws InvalidArgumentException
     */
    private function validateAbSettings(string $type, array $abSettings): void
    {
        if ($type === 'ab' && empty($abSettings)) {
            throw new InvalidArgumentException('A/B test settings are required when campaign type is "ab".');
        }

        if ($type !== 'ab' && ! empty($abSettings)) {
            throw new InvalidArgumentException('A/B test settings can only be used with campaign type "ab".');
        }

        if (! empty($abSettings)) {
            $requiredFields = ['test_type', 'send_size'];
            foreach ($requiredFields as $field) {
                if (! isset($abSettings[$field])) {
                    throw new InvalidArgumentException("A/B test setting '{$field}' is required.");
                }
            }

            $validTestTypes = ['subject', 'from_name', 'content'];
            if (! in_array($abSettings['test_type'], $validTestTypes, true)) {
                throw new InvalidArgumentException(
                    "Invalid A/B test type '{$abSettings['test_type']}'. Valid types: ".implode(', ', $validTestTypes)
                );
            }

            if (! is_int($abSettings['send_size']) || $abSettings['send_size'] < 10 || $abSettings['send_size'] > 50) {
                throw new InvalidArgumentException('A/B test send size must be an integer between 10 and 50.');
            }
        }
    }
}
