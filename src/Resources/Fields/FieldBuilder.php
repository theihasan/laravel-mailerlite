<?php

declare(strict_types=1);

namespace Ihasan\LaravelMailerlite\Resources\Fields;

use Ihasan\LaravelMailerlite\DTOs\FieldDTO;

/**
 * Field Builder
 *
 * Provides a fluent, plain-English API for building custom field operations.
 * This class enables method chaining that reads like natural language.
 *
 * Example usage:
 *   MailerLite::fields()
 *       ->name('age')
 *       ->type('number')
 *       ->withTitle('Age')
 *       ->create();
 */
class FieldBuilder
{
    /**
     * Current field name being built
     */
    protected ?string $name = null;

    /**
     * Current field type being built
     */
    protected ?string $type = null;

    /**
     * Current field title being built
     */
    protected ?string $title = null;

    /**
     * Current field default value being built
     */
    protected mixed $defaultValue = null;

    /**
     * Current field options being built
     */
    protected array $options = [];

    /**
     * Whether the field is required
     */
    protected bool $required = false;

    /**
     * Create a new field builder instance.
     */
    public function __construct(
        protected FieldService $service
    ) {}

    /**
     * Set the field name.
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Alias for name() - more natural in some contexts.
     */
    public function named(string $name): static
    {
        return $this->name($name);
    }

    /**
     * Set the field type.
     */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set field type to text.
     */
    public function asText(): static
    {
        $this->type = 'text';

        return $this;
    }

    /**
     * Set field type to number.
     */
    public function asNumber(): static
    {
        $this->type = 'number';

        return $this;
    }

    /**
     * Set field type to date.
     */
    public function asDate(): static
    {
        $this->type = 'date';

        return $this;
    }

    /**
     * Set field type to boolean.
     */
    public function asBoolean(): static
    {
        $this->type = 'boolean';

        return $this;
    }

    /**
     * Create a dropdown/select field with options.
     */
    public function asSelect(array $options): static
    {
        $this->type = 'text';
        $this->options = array_merge($this->options, [
            'type' => 'select',
            'values' => $options,
        ]);

        return $this;
    }

    /**
     * Set the field title (which updates the field name in MailerLite).
     * 
     * Note: MailerLite does not support separate title fields. The name field
     * serves as both the identifier and display name.
     */
    public function withTitle(string $title): static
    {
        // In MailerLite, the title IS the name - there's no separate title field
        $this->name = $title;

        return $this;
    }

    /**
     * Alias for withTitle() - shorter version.
     */
    public function title(string $title): static
    {
        return $this->withTitle($title);
    }

    /**
     * Set the field default value.
     */
    public function withDefault(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Alias for withDefault() - shorter version.
     */
    public function defaultValue(mixed $value): static
    {
        return $this->withDefault($value);
    }

    /**
     * Add options to the field.
     */
    public function withOptions(array $options): static
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Add a single option to the field.
     */
    public function withOption(string $key, mixed $value): static
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Make the field required.
     */
    public function required(): static
    {
        $this->required = true;

        return $this;
    }

    /**
     * Make the field optional.
     */
    public function optional(): static
    {
        $this->required = false;

        return $this;
    }

    /**
     * Set minimum length for text fields.
     */
    public function minLength(int $length): static
    {
        $this->options['min_length'] = $length;

        return $this;
    }

    /**
     * Set maximum length for text fields.
     */
    public function maxLength(int $length): static
    {
        $this->options['max_length'] = $length;

        return $this;
    }

    /**
     * Set minimum value for number fields.
     */
    public function minValue(int|float $value): static
    {
        $this->options['min_value'] = $value;

        return $this;
    }

    /**
     * Set maximum value for number fields.
     */
    public function maxValue(int|float $value): static
    {
        $this->options['max_value'] = $value;

        return $this;
    }

    /**
     * Set field as email validation.
     */
    public function asEmail(): static
    {
        $this->type = 'text';
        $this->options['validation'] = 'email';

        return $this;
    }

    /**
     * Set field as phone validation.
     */
    public function asPhone(): static
    {
        $this->type = 'text';
        $this->options['validation'] = 'phone';

        return $this;
    }

    /**
     * Create the field.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldCreateException
     * @throws \InvalidArgumentException
     */
    public function create(): array
    {
        $dto = $this->toDTO();

        return $this->service->create($dto);
    }

    /**
     * Update a field by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldUpdateException
     */
    public function update(string $id): array
    {
        // Get the existing field to merge with new values
        $existingField = $this->service->getById($id);
        
        if (!$existingField) {
            throw \Ihasan\LaravelMailerlite\Exceptions\FieldNotFoundException::withId($id);
        }
        
        // Create DTO with existing values as defaults, overridden by builder values
        $dto = new \Ihasan\LaravelMailerlite\DTOs\FieldDTO(
            name: $this->name ?? $existingField['name'],
            type: $this->type ?? $existingField['type'],
            title: $this->title ?? $existingField['title'],
            defaultValue: $this->defaultValue ?? $existingField['default_value'],
            options: !empty($this->options) ? $this->options : ($existingField['options'] ?? []),
            required: $this->required !== false ? $this->required : ($existingField['required'] ?? false)
        );

        return $this->service->update($id, $dto);
    }

    /**
     * Delete a field by ID.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldNotFoundException
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldDeleteException
     */
    public function delete(string $id): bool
    {
        return $this->service->delete($id);
    }

    /**
     * Get a field by ID.
     */
    public function find(string $id): ?array
    {
        return $this->service->get($id);
    }

    /**
     * Find a field by name.
     */
    public function findByName(string $name): ?array
    {
        return $this->service->findByName($name);
    }

    /**
     * Get all fields with optional filters.
     */
    public function list(array $filters = []): array
    {
        return $this->service->list($filters);
    }

    /**
     * Get all fields (no filters).
     */
    public function all(): array
    {
        return $this->service->list();
    }

    /**
     * Get field usage statistics.
     *
     * @throws \Ihasan\LaravelMailerlite\Exceptions\FieldNotFoundException
     */
    public function getUsage(string $id): array
    {
        return $this->service->getUsage($id);
    }

    // Static factory methods for common field types

    /**
     * Create a text field builder.
     */
    public static function text(string $name, ?string $title = null): static
    {
        $builder = new static(app(FieldService::class));
        $builder->name($name)->asText();

        if ($title) {
            $builder->withTitle($title);
        }

        return $builder;
    }

    /**
     * Create a number field builder.
     */
    public static function number(string $name, ?string $title = null): static
    {
        $builder = new static(app(FieldService::class));
        $builder->name($name)->asNumber();

        if ($title) {
            $builder->withTitle($title);
        }

        return $builder;
    }

    /**
     * Create a date field builder.
     */
    public static function date(string $name, ?string $title = null): static
    {
        $builder = new static(app(FieldService::class));
        $builder->name($name)->asDate();

        if ($title) {
            $builder->withTitle($title);
        }

        return $builder;
    }

    /**
     * Create a boolean field builder.
     */
    public static function boolean(string $name, ?string $title = null): static
    {
        $builder = new static(app(FieldService::class));
        $builder->name($name)->asBoolean();

        if ($title) {
            $builder->withTitle($title);
        }

        return $builder;
    }

    /**
     * Create a select field builder.
     */
    public static function select(string $name, array $options, ?string $title = null): static
    {
        $builder = new static(app(FieldService::class));
        $builder->name($name)->asSelect($options);

        if ($title) {
            $builder->withTitle($title);
        }

        return $builder;
    }

    /**
     * Convert current builder state to DTO.
     *
     * @throws \InvalidArgumentException
     */
    public function toDTO(): FieldDTO
    {
        if (! $this->name) {
            throw new \InvalidArgumentException('Name is required to create FieldDTO');
        }

        if (! $this->type) {
            throw new \InvalidArgumentException('Type is required to create FieldDTO');
        }

        return new FieldDTO(
            name: $this->name,
            type: $this->type,
            title: $this->title,
            defaultValue: $this->defaultValue,
            options: $this->options,
            required: $this->required,
        );
    }

    /**
     * Reset the builder to initial state.
     */
    public function reset(): static
    {
        $this->name = null;
        $this->type = null;
        $this->title = null;
        $this->defaultValue = null;
        $this->options = [];
        $this->required = false;

        return $this;
    }

    /**
     * Create a new builder instance from this one.
     */
    public function fresh(): static
    {
        return new static($this->service);
    }

    /**
     * Magic method to handle method chaining with "and" for readability.
     *
     * Examples:
     *   ->name('age')->andAsNumber()->andWithTitle('Age')
     *   ->asText()->andRequired()->andCreate()
     */
    public function __call(string $method, array $arguments): mixed
    {
        // Handle "and" prefixed methods for natural language chaining
        if (str_starts_with($method, 'and')) {
            $actualMethod = lcfirst(substr($method, 3));

            if (method_exists($this, $actualMethod)) {
                return $this->$actualMethod(...$arguments);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist on ".static::class);
    }
}
