# Laravel MailerLite SDK Wrapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/theihasan/laravel-mailerlite.svg?style=flat-square)](https://packagist.org/packages/theihasan/laravel-mailerlite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/theihasan/laravel-mailerlite/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/theihasan/laravel-mailerlite/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/theihasan/laravel-mailerlite/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/theihasan/laravel-mailerlite/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/theihasan/laravel-mailerlite.svg?style=flat-square)](https://packagist.org/packages/theihasan/laravel-mailerlite)

A comprehensive Laravel wrapper for the official MailerLite PHP SDK, providing a fluent, plain-English API for managing subscribers, campaigns, groups, fields, segments, webhooks, and automations. Built with strong architecture patterns including DTOs, contracts, services, and comprehensive exception handling.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-mailerlite.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-mailerlite)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

Install the package via Composer:

```bash
composer require theihasan/laravel-mailerlite
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-mailerlite-config"
```

Add your MailerLite API key to your `.env` file:

```env
MAILERLITE_API_KEY=your_api_key_here
```

## Configuration

The configuration file `config/mailerlite.php` contains:

```php
return [
    'key' => env('MAILERLITE_API_KEY'),
    'url' => env('MAILERLITE_API_URL', 'https://connect.mailerlite.com/api/'),
    'timeout' => env('MAILERLITE_TIMEOUT', 30),
];
```

## Fluent API Quick Start

This package provides a fluent, plain-English API for all MailerLite operations:

```php
use MailerLite;

// Subscribe a user to a group with method chaining
MailerLite::subscribers()
    ->email('jane@example.com')
    ->named('Jane Doe')
    ->subscribe()
    ->toGroup('Early Adopters');

// Create and schedule a campaign
MailerLite::campaigns()
    ->draft()
    ->subject('Weekly Jobs Newsletter')
    ->from('Jobs Bot', 'jobs@example.com')
    ->html('<h1>New job opportunities</h1>')
    ->toGroup('Developers')
    ->scheduleAt(now()->addDay());

// Setup a webhook
MailerLite::webhooks()
    ->on('subscriber.created')
    ->url('https://yourapp.com/webhooks/mailerlite')
    ->create();
```

## Architecture Overview

This package follows Laravel best practices with:

- **Manager Layer**: `MailerLiteManager` handles SDK initialization
- **Contracts**: Interfaces for all services to enable mocking
- **DTOs**: Data Transfer Objects for all inputs with validation
- **Services**: Per-resource services (SubscriberService, CampaignService, etc.)
- **Builders**: Fluent method chaining for readable API calls
- **Exceptions**: Custom exceptions with granular error mapping
- **Facade**: Clean, expressive facade interface

## Manager Layer

The `MailerLiteManager` class is responsible for initializing and managing the MailerLite SDK instance. It handles:

- **API Key Validation**: Ensures API key is present and valid
- **SDK Initialization**: Creates and configures the MailerLite SDK client
- **Connection Testing**: Validates the API connection on initialization
- **Configuration Management**: Handles timeout and base URL configuration

### Basic Usage

```php
use Ihasan\LaravelMailerlite\Manager\MailerLiteManager;

// Create manager with API key
$manager = new MailerLiteManager('your-api-key');

// Create manager with options
$manager = new MailerLiteManager('your-api-key', [
    'timeout' => 60,
    'base_url' => 'https://custom.api.url'
]);

// Create from configuration array
$manager = MailerLiteManager::fromConfig([
    'key' => 'your-api-key',
    'timeout' => 30,
    'url' => 'https://connect.mailerlite.com/api/'
]);

// Get the SDK client
$client = $manager->getClient();
```

### Exception Handling

The manager throws `MailerLiteAuthenticationException` when:

- API key is missing or empty
- API key is invalid or revoked
- API key has insufficient permissions

## Campaign Management

The Campaign API provides a fluent interface for creating, scheduling, and managing email campaigns.

### Creating and Sending Campaigns

```php
use MailerLite;

// Create and send a campaign immediately
MailerLite::campaigns()
    ->subject('Weekly Newsletter')
    ->from('Newsletter Team', 'newsletter@example.com')
    ->html('<h1>This Week in Tech</h1><p>Latest updates...</p>')
    ->toGroup('newsletter-subscribers')
    ->send();

// Create a draft campaign
$campaign = MailerLite::campaigns()
    ->draft()
    ->subject('Product Launch')
    ->from('Marketing', 'marketing@example.com')
    ->html('<h1>Introducing Our New Product</h1>')
    ->plain('Introducing Our New Product - Latest updates...')
    ->toGroups(['customers', 'prospects'])
    ->create();

// Schedule a campaign for later
MailerLite::campaigns()
    ->subject('Weekend Sale')
    ->from('Sales Team', 'sales@example.com')
    ->content('<h1>50% Off Everything!</h1>', '50% Off Everything!')
    ->toSegment('active-customers')
    ->scheduleAt(now()->addDays(2))
    ->schedule();
```

### Advanced Campaign Features

```php
// A/B Test Campaign
MailerLite::campaigns()
    ->subject('A/B Test Subject')
    ->from('Test Team', 'test@example.com')
    ->html('<h1>Version A</h1>')
    ->toGroup('test-group')
    ->abTest([
        'test_type' => 'subject',
        'send_size' => 25  // Send to 25% of recipients for testing
    ])
    ->create();

// Campaign with custom settings
MailerLite::campaigns()
    ->subject('Custom Campaign')
    ->from('Custom', 'custom@example.com')
    ->html('<h1>Custom Content</h1>')
    ->toGroup('subscribers')
    ->withSettings([
        'track_opens' => true,
        'track_clicks' => true,
        'google_analytics' => true
    ])
    ->create();
```

### Managing Existing Campaigns

```php
// Find a campaign
$campaign = MailerLite::campaigns()->find('campaign-id');

// Update a campaign
MailerLite::campaigns()
    ->subject('Updated Subject')
    ->from('Updated Sender', 'updated@example.com')
    ->html('<h1>Updated Content</h1>')
    ->update('campaign-id');

// Send an existing campaign
MailerLite::campaigns()->sendById('campaign-id');

// Schedule an existing campaign
MailerLite::campaigns()->scheduleById('campaign-id', now()->addHour());

// Cancel a scheduled campaign
MailerLite::campaigns()->cancel('campaign-id');

// Delete a campaign
MailerLite::campaigns()->delete('campaign-id');
```

### Campaign Analytics

```php
// Get campaign statistics
$stats = MailerLite::campaigns()->stats('campaign-id');
// Returns: sent, opened, clicked, bounced, etc.

// Get campaign subscribers
$subscribers = MailerLite::campaigns()->subscribers('campaign-id');

// Get subscribers with filters
$openedSubscribers = MailerLite::campaigns()->subscribers('campaign-id', [
    'filter' => 'opened'
]);
```

### Listing Campaigns

```php
// Get all campaigns
$campaigns = MailerLite::campaigns()->all();

// Get campaigns with filters
$sentCampaigns = MailerLite::campaigns()->list([
    'filter[status]' => 'sent',
    'limit' => 50
]);
```

### Fluent Method Chaining

The Campaign Builder supports natural language chaining:

```php
MailerLite::campaigns()
    ->draft()
    ->subject('Monthly Update')
    ->andFrom('Team Lead', 'lead@example.com')
    ->andHtml('<h1>Monthly Report</h1>')
    ->andToGroup('team-members')
    ->andScheduleIn(60) // Schedule in 60 minutes
    ->schedule();
```

### Using DTOs Directly

For more control, you can use the CampaignDTO directly:

```php
use Ihasan\LaravelMailerlite\DTOs\CampaignDTO;

$campaignData = new CampaignDTO(
    subject: 'Direct DTO Campaign',
    fromName: 'Direct Sender',
    fromEmail: 'direct@example.com',
    html: '<h1>Using DTO directly</h1>',
    groups: ['group-1', 'group-2'],
    scheduleAt: new DateTime('+2 hours')
);

$campaign = MailerLite::campaigns()->create($campaignData);
```

## Contracts (Interfaces)

This package provides comprehensive interfaces for all services, enabling easy mocking and testing:

### Available Contracts

- `MailerLiteInterface` - Main package interface
- `SubscribersInterface` - Subscriber operations
- `CampaignsInterface` - Campaign management
- `GroupsInterface` - Group management
- `FieldsInterface` - Custom field management
- `SegmentsInterface` - Segment operations
- `WebhooksInterface` - Webhook management
- `AutomationsInterface` - Automation operations

### Usage with Dependency Injection

```php
use Ihasan\LaravelMailerlite\Contracts\SubscribersInterface;

class NewsletterService
{
    public function __construct(
        private SubscribersInterface $subscribers
    ) {}

    public function addSubscriber(string $email, string $name): void
    {
        $subscriber = new SubscriberDTO($email, $name);
        $this->subscribers->create($subscriber);
    }
}
```

### Testing with Contracts

```php
use Ihasan\LaravelMailerlite\Contracts\SubscribersInterface;

// In your test
$mock = Mockery::mock(SubscribersInterface::class);
$mock->shouldReceive('create')->once()->andReturn(['id' => '123']);

$this->app->instance(SubscribersInterface::class, $mock);
```

## Testing

```bash
composer test
```

## Git Subtree Workflow

This package is maintained using git subtree. Use these commands for development:

### Setup (one time)
```bash
git config alias.mailerlite-pull "subtree pull --prefix=packages/laravel-mailerlite https://github.com/theihasan/laravel-mailerlite.git main --squash"
git config alias.mailerlite-push "subtree push --prefix=packages/laravel-mailerlite https://github.com/theihasan/laravel-mailerlite.git main"
```

### Development workflow
```bash
# Pull latest changes from remote
git mailerlite-pull

# After making changes and committing locally
git mailerlite-push
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Abul Hassan](https://github.com/theihasan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
