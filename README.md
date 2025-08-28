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

## Automation Management

The Automation API provides a fluent interface for creating, managing, and monitoring email automations and workflows.

### Creating Basic Automations

```php
use MailerLite;

// Create a simple welcome automation
MailerLite::automations()
    ->create('Welcome Series')
    ->description('Welcome new subscribers with a series of emails')
    ->whenSubscriberJoinsGroup('newsletter')
    ->sendEmail('welcome-template')
    ->delayDays(3)
    ->sendEmail('tips-template')
    ->delayWeeks(1)
    ->sendEmail('resources-template')
    ->start();

// Create a birthday automation
MailerLite::automations()
    ->create('Birthday Campaign')
    ->whenDateReached('birthday', 0) // Trigger on birthday
    ->sendEmail('birthday-template')
    ->addTag('birthday-sent')
    ->save();
```

### Advanced Automation Workflows

```php
// Create a re-engagement automation with conditions
MailerLite::automations()
    ->create('Re-engagement Campaign')
    ->description('Win back inactive subscribers')
    ->whenSubscriberUpdatesField('last_active')
    ->delayDays(30)
    ->ifField('engagement_score', 'less_than', 50)
    ->sendEmail('reengagement-template')
    ->delayDays(7)
    ->ifField('last_opened', 'less_than', '30 days ago')
    ->addTag('inactive')
    ->callWebhook('https://yourapp.com/webhooks/inactive-subscriber')
    ->start();

// API-triggered automation
MailerLite::automations()
    ->create('Purchase Follow-up')
    ->whenApiCalled('/api/purchase-trigger')
    ->delayHours(2)
    ->sendEmail('thank-you-template')
    ->delayDays(7)
    ->sendEmail('review-request-template')
    ->delayDays(30)
    ->sendEmail('upsell-template')
    ->withSettings([
        'timezone' => 'America/New_York',
        'frequency_cap' => 3
    ])
    ->start();
```

### Automation Triggers

```php
// Subscriber-based triggers
MailerLite::automations()
    ->create('Onboarding Flow')
    ->whenSubscriberSubscribes()              // When someone subscribes
    ->whenSubscriberJoinsGroup('premium')     // When joins specific group
    ->whenSubscriberUpdatesField('country')   // When updates a field
    ->sendEmail('onboarding-template')
    ->start();

// Date-based triggers
MailerLite::automations()
    ->create('Anniversary Campaign')
    ->whenDateReached('signup_date', 365)     // 365 days after signup
    ->sendEmail('anniversary-template')
    ->start();

// Webhook triggers
MailerLite::automations()
    ->create('External Event Automation')
    ->whenWebhookReceived('https://yourapp.com/webhook-endpoint')
    ->sendEmail('event-response-template')
    ->start();
```

### Automation Actions and Steps

```php
// Email actions
MailerLite::automations()
    ->create('Email Sequence')
    ->whenSubscriberJoinsGroup('course')
    ->sendEmail('welcome-template')           // Send template
    ->sendCampaign('intro-campaign')          // Send existing campaign
    ->start();

// Delays and timing
MailerLite::automations()
    ->create('Timed Sequence')
    ->whenSubscriberJoinsGroup('trial')
    ->sendEmail('welcome-template')
    ->delayMinutes(30)                        // 30 minutes
    ->delayHours(24)                          // 24 hours
    ->delayDays(7)                            // 7 days
    ->delayWeeks(2)                           // 2 weeks
    ->delay(3, 'months')                      // Custom delay
    ->sendEmail('followup-template')
    ->start();

// Conditions and branching
MailerLite::automations()
    ->create('Conditional Flow')
    ->whenSubscriberJoinsGroup('leads')
    ->ifField('country', 'equals', 'US')
    ->sendEmail('us-specific-template')
    ->condition([
        ['field' => 'age', 'operator' => 'greater_than', 'value' => 25],
        ['field' => 'income', 'operator' => 'greater_than', 'value' => 50000]
    ])
    ->sendEmail('premium-offer-template')
    ->start();

// Tags and field updates
MailerLite::automations()
    ->create('Tagging Automation')
    ->whenSubscriberJoinsGroup('webinar')
    ->addTag('webinar-attendee')
    ->updateField('last_webinar', 'now')
    ->removeTag('prospect')
    ->sendEmail('webinar-followup')
    ->start();

// Webhook actions
MailerLite::automations()
    ->create('Integration Automation')
    ->whenSubscriberUpdatesField('purchase_status')
    ->callWebhook('https://yourapp.com/api/sync-customer', [
        'action' => 'sync',
        'source' => 'mailerlite'
    ])
    ->start();
```

### Managing Existing Automations

```php
// Find an automation
$automation = MailerLite::automations()->find('automation-id');

// Update an automation
MailerLite::automations()
    ->create('Updated Automation')
    ->description('Updated description')
    ->whenSubscriberJoinsGroup('updated-group')
    ->sendEmail('updated-template')
    ->update('automation-id');

// Start/stop automations
MailerLite::automations()->startById('automation-id');
MailerLite::automations()->stopById('automation-id');

// Enable/disable automations
MailerLite::automations()->enableById('automation-id');
MailerLite::automations()->disableById('automation-id');

// Pause/resume automations
MailerLite::automations()->pauseById('automation-id');
MailerLite::automations()->resumeById('automation-id');

// Delete an automation
MailerLite::automations()->delete('automation-id');
```

### Automation Analytics and Monitoring

```php
// Get automation statistics
$stats = MailerLite::automations()->stats('automation-id');
// Returns: subscribers_count, completed_count, active_count, etc.

// Get subscribers in automation
$subscribers = MailerLite::automations()->subscribers('automation-id');

// Get subscribers with filters
$activeSubscribers = MailerLite::automations()->subscribers('automation-id', [
    'filter[status]' => 'active'
]);

// Get automation activity
$activity = MailerLite::automations()->activity('automation-id');
```

### Listing Automations

```php
// Get all automations
$automations = MailerLite::automations()->all();

// Get automations with filters
$activeAutomations = MailerLite::automations()->list([
    'filter[status]' => 'active',
    'limit' => 50
]);
```

### Automation Settings and Configuration

```php
// Set timezone and send time restrictions
MailerLite::automations()
    ->create('Time-Sensitive Automation')
    ->whenSubscriberJoinsGroup('newsletter')
    ->timezone('America/New_York')
    ->sendTimeBetween('09:00', '17:00')
    ->frequencyCap(5)  // Max 5 emails per subscriber
    ->sendEmail('newsletter-template')
    ->start();

// Advanced settings
MailerLite::automations()
    ->create('Advanced Automation')
    ->whenSubscriberJoinsGroup('premium')
    ->withSettings([
        'timezone' => 'UTC',
        'send_time' => ['start' => '08:00', 'end' => '20:00'],
        'frequency_cap' => 10,
        'track_opens' => true,
        'track_clicks' => true
    ])
    ->withConditions([
        ['field' => 'subscription_type', 'operator' => 'equals', 'value' => 'premium'],
        ['field' => 'country', 'operator' => 'not_equals', 'value' => 'blocked_country']
    ])
    ->sendEmail('premium-welcome')
    ->start();
```

### Fluent Method Chaining

The Automation Builder supports natural language chaining with "and" and "then" prefixes:

```php
MailerLite::automations()
    ->create('Natural Language Flow')
    ->andDescription('Reads like natural language')
    ->andWhenSubscriberJoinsGroup('newsletter')
    ->thenSendEmail('welcome-template')
    ->thenDelayDays(1)
    ->thenAddTag('welcomed')
    ->thenIfField('country', 'equals', 'US')
    ->thenSendEmail('us-specific-content')
    ->start();
```

### Using DTOs Directly

For more control, you can use the AutomationDTO directly:

```php
use Ihasan\LaravelMailerlite\DTOs\AutomationDTO;

$automationData = new AutomationDTO(
    name: 'Direct DTO Automation',
    enabled: true,
    triggers: [
        ['type' => 'subscriber', 'event' => 'joins_group', 'target' => 'newsletter']
    ],
    steps: [
        ['type' => 'email', 'template_id' => 'welcome-template'],
        ['type' => 'delay', 'duration' => 1, 'unit' => 'days'],
        ['type' => 'email', 'template_id' => 'followup-template']
    ],
    description: 'Created using DTO directly',
    settings: ['timezone' => 'UTC']
);

$automation = MailerLite::automations()->save($automationData);
```

## Webhook Management

The Webhook API provides a fluent interface for creating, managing, and monitoring webhooks to receive real-time notifications from MailerLite.

### Creating Basic Webhooks

```php
use MailerLite;

// Create a webhook for subscriber events
MailerLite::webhooks()
    ->on('subscriber.created')
    ->url('https://yourapp.com/webhooks/subscriber-created')
    ->create();

// Create a webhook for campaign events
MailerLite::webhooks()
    ->on('campaign.sent')
    ->url('https://yourapp.com/webhooks/campaign-sent')
    ->withSecret('your-webhook-secret')
    ->create();

// Create a webhook with custom settings
MailerLite::webhooks()
    ->on('subscriber.unsubscribed')
    ->url('https://yourapp.com/webhooks/unsubscribed')
    ->named('Unsubscribe Notifications')
    ->timeout(60)
    ->retries(5)
    ->verifySSL(true)
    ->asJson()
    ->create();
```

### Webhook Event Types

```php
// Subscriber events
MailerLite::webhooks()
    ->onSubscriber('created')                    // subscriber.created
    ->onSubscriber('updated')                    // subscriber.updated
    ->onSubscriber('unsubscribed')               // subscriber.unsubscribed
    ->onSubscriber('bounced')                    // subscriber.bounced
    ->onSubscriber('complained')                 // subscriber.complained
    ->url('https://yourapp.com/webhooks/subscriber')
    ->create();

// Campaign events
MailerLite::webhooks()
    ->onCampaign('sent')                         // campaign.sent
    ->onCampaign('opened')                       // campaign.opened
    ->onCampaign('clicked')                      // campaign.clicked
    ->onCampaign('bounced')                      // campaign.bounced
    ->onCampaign('delivered')                    // campaign.delivered
    ->url('https://yourapp.com/webhooks/campaign')
    ->create();

// Automation events
MailerLite::webhooks()
    ->onAutomation('subscriber_added')           // automation.subscriber_added
    ->onAutomation('subscriber_completed')       // automation.subscriber_completed
    ->onAutomation('email_sent')                 // automation.email_sent
    ->url('https://yourapp.com/webhooks/automation')
    ->create();

// Form and group events
MailerLite::webhooks()
    ->onForm('submitted')                        // form.submitted
    ->url('https://yourapp.com/webhooks/form')
    ->create();

MailerLite::webhooks()
    ->onGroup('subscriber_added')                // group.subscriber_added
    ->url('https://yourapp.com/webhooks/group')
    ->create();
```

### Convenient Event-Specific Methods

```php
// Quick webhook creation for common events
MailerLite::webhooks()
    ->onSubscriberCreated('https://yourapp.com/webhooks/new-subscriber')
    ->withSecret('secret-key')
    ->create();

MailerLite::webhooks()
    ->onSubscriberUpdated('https://yourapp.com/webhooks/updated-subscriber')
    ->create();

MailerLite::webhooks()
    ->onSubscriberUnsubscribed('https://yourapp.com/webhooks/unsubscribed')
    ->create();

MailerLite::webhooks()
    ->onCampaignSent('https://yourapp.com/webhooks/campaign-sent')
    ->create();

MailerLite::webhooks()
    ->onCampaignOpened('https://yourapp.com/webhooks/campaign-opened')
    ->create();

MailerLite::webhooks()
    ->onCampaignClicked('https://yourapp.com/webhooks/campaign-clicked')
    ->create();
```

### Advanced Webhook Configuration

```php
// Webhook with custom headers and settings
MailerLite::webhooks()
    ->on('subscriber.created')
    ->url('https://yourapp.com/webhooks/subscriber')
    ->named('Subscriber Webhook')
    ->withHeaders([
        'Authorization' => 'Bearer your-api-token',
        'X-Custom-Header' => 'custom-value'
    ])
    ->withSecret('webhook-signature-secret')
    ->timeout(120)                               // 120 seconds timeout
    ->retries(3)                                 // 3 retry attempts
    ->verifySSL(true)                           // Verify SSL certificates
    ->asJson()                                  // Send as JSON
    ->create();

// Webhook with form data content type
MailerLite::webhooks()
    ->on('campaign.opened')
    ->url('https://yourapp.com/webhooks/opened')
    ->asForm()                                  // Send as form data
    ->withSettings([
        'verify_ssl' => false,
        'content_type' => 'application/x-www-form-urlencoded'
    ])
    ->create();
```

### Managing Existing Webhooks

```php
// Find a webhook
$webhook = MailerLite::webhooks()->find('webhook-id');

// Update a webhook
MailerLite::webhooks()
    ->on('subscriber.updated')
    ->url('https://newdomain.com/webhooks/subscriber')
    ->named('Updated Subscriber Webhook')
    ->update('webhook-id');

// Enable/disable webhooks
MailerLite::webhooks()->enable('webhook-id');
MailerLite::webhooks()->disable('webhook-id');

// Delete a webhook
MailerLite::webhooks()->delete('webhook-id');
```

### Webhook Testing and Monitoring

```php
// Test a webhook (sends test payload)
$testResult = MailerLite::webhooks()->test('webhook-id');

// Get webhook delivery logs
$logs = MailerLite::webhooks()->logs('webhook-id');

// Get webhook delivery logs with filters
$failedLogs = MailerLite::webhooks()->logs('webhook-id', [
    'filter[status]' => 'failed',
    'limit' => 50
]);

// Get webhook statistics
$stats = MailerLite::webhooks()->stats('webhook-id');
// Returns: delivery_count, success_count, failure_count, etc.
```

### Finding and Managing Webhooks by URL

```php
// Find webhook by URL
$webhook = MailerLite::webhooks()->findByUrl('https://yourapp.com/webhooks/subscriber');

// Find webhook by URL and specific event
$webhook = MailerLite::webhooks()->findByUrl(
    'https://yourapp.com/webhooks/subscriber',
    'subscriber.created'
);

// Delete webhook by URL
MailerLite::webhooks()->deleteByUrl('https://yourapp.com/webhooks/subscriber');

// Delete specific webhook by URL and event
MailerLite::webhooks()->deleteByUrl(
    'https://yourapp.com/webhooks/subscriber',
    'subscriber.created'
);
```

### Listing Webhooks

```php
// Get all webhooks
$webhooks = MailerLite::webhooks()->all();

// Get webhooks with filters
$subscriberWebhooks = MailerLite::webhooks()->list([
    'filter[event]' => 'subscriber.created',
    'limit' => 25
]);
```

### Webhook Security

```php
// Create webhook with signature verification
MailerLite::webhooks()
    ->on('subscriber.created')
    ->url('https://yourapp.com/webhooks/secure')
    ->withSecret('your-webhook-secret-key')
    ->create();

// In your webhook handler (Laravel example):
public function handleWebhook(Request $request)
{
    $signature = $request->header('X-MailerLite-Signature');
    $payload = $request->getContent();
    $secret = 'your-webhook-secret-key';
    
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    
    if (!hash_equals($expectedSignature, $signature)) {
        abort(403, 'Invalid signature');
    }
    
    // Process webhook payload
    $data = $request->json()->all();
    
    return response()->json(['status' => 'success']);
}
```

### Webhook Payload Examples

```php
// Example webhook payloads you'll receive:

// Subscriber Created
{
    "event": "subscriber.created",
    "data": {
        "id": "123",
        "email": "user@example.com",
        "name": "John Doe",
        "status": "active",
        "subscribed_at": "2024-01-01T12:00:00Z",
        "fields": {
            "country": "US",
            "city": "New York"
        },
        "groups": [
            {"id": "456", "name": "Newsletter"}
        ]
    }
}

// Campaign Sent
{
    "event": "campaign.sent",
    "data": {
        "campaign_id": "789",
        "campaign_name": "Weekly Newsletter",
        "sent_at": "2024-01-01T12:00:00Z",
        "stats": {
            "sent": 1000,
            "delivered": 995
        }
    }
}
```

### Fluent Method Chaining

The Webhook Builder supports natural language chaining with "and" prefixes:

```php
MailerLite::webhooks()
    ->on('subscriber.created')
    ->andUrl('https://yourapp.com/webhooks/subscriber')
    ->andNamed('Subscriber Notifications')
    ->andWithSecret('webhook-secret')
    ->andTimeout(60)
    ->create();
```

### Using DTOs Directly

For more control, you can use the WebhookDTO directly:

```php
use Ihasan\LaravelMailerlite\DTOs\WebhookDTO;

$webhookData = new WebhookDTO(
    event: 'subscriber.created',
    url: 'https://yourapp.com/webhooks/subscriber',
    enabled: true,
    name: 'Subscriber Webhook',
    headers: ['Authorization' => 'Bearer token'],
    secret: 'webhook-secret',
    timeout: 60,
    retryCount: 3
);

$webhook = MailerLite::webhooks()->create($webhookData);
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
